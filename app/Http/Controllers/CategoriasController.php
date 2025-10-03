<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoriasController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Categoria::withCount('productos');
        $logs = collect();
        $usuarios = collect();

        if ($request->has('eliminados')) {
            $query = $query->onlyTrashed();
            
            // Obtener logs de auditoría para categorías eliminadas con filtros
            $logsQuery = Auditoria::where('model_type', Categoria::class)
                ->whereIn('action', ['delete', 'restore', 'forceDelete'])
                ->with('user');

            if ($request->filled('log_buscar')) {
                $logBuscar = $request->input('log_buscar');
                $logsQuery = $logsQuery->where(function($q) use ($logBuscar) {
                    $q->where('description', 'like', "%$logBuscar%")
                      ->orWhere('observacion', 'like', "%$logBuscar%")
                      ->orWhereHas('user', function($userQuery) use ($logBuscar) {
                          $userQuery->where('name', 'like', "%$logBuscar%");
                      });
                });
            }

            if ($request->filled('log_accion')) {
                $logsQuery = $logsQuery->where('action', $request->input('log_accion'));
            }

            if ($request->filled('log_usuario')) {
                $logsQuery = $logsQuery->whereHas('user', function($q) use ($request) {
                    $q->where('id', $request->input('log_usuario'));
                });
            }

            if ($request->filled('log_fecha_desde')) {
                $logsQuery = $logsQuery->whereDate('created_at', '>=', $request->input('log_fecha_desde'));
            }

            if ($request->filled('log_fecha_hasta')) {
                $logsQuery = $logsQuery->whereDate('created_at', '<=', $request->input('log_fecha_hasta'));
            }

            $logs = $logsQuery->orderBy('created_at', 'desc')
                ->paginate($request->input('log_per_page', 10))
                ->withQueryString();

            $usuarios = \App\Models\User::orderBy('name')->get();
        }

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%$buscar%")
                  ->orWhere('descripcion', 'like', "%$buscar%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->input('activo') === '1');
        }

        $perPage = $request->input('per_page', 10);
        $categorias = $query->orderBy('nombre', 'asc')->paginate($perPage)->withQueryString();

        // Obtener totales para los tabs
        $totalActivas = Categoria::count();
        $totalEliminadas = Categoria::onlyTrashed()->count();

        return view('categorias.index', compact('categorias', 'logs', 'usuarios', 'totalActivas', 'totalEliminadas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:500',
            'color' => 'required|string|max:7',
            'activo' => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es requerido.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
            'color.required' => 'El color es requerido.',
            'activo.required' => 'El estado es requerido.',
        ]);

        \DB::beginTransaction();
        try {
            $data = $request->all();
            $data['created_by'] = auth()->id();

            $categoria = Categoria::create($data);

            $this->registrarAuditoria('create', $categoria, null, $data, 'Categoría creada');

            \DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría creada exitosamente.',
                    'redirect' => route('categorias.index')
                ]);
            }

            return redirect()->route('categorias.index')->with('success', 'Categoría creada exitosamente.');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error al crear categoría: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la categoría.'
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Error al crear la categoría.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $categoria = Categoria::withTrashed()
            ->withCount('productos')
            ->with([
                'productos' => function($query) {
                    $query->orderBy('nombre');
                },
                'creador',
                'modificador',
                'auditorias' => function($query) {
                    $query->with('user')->orderBy('created_at', 'desc');
                }
            ])
            ->findOrFail($id);

        return view('categorias.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::withTrashed()->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:500',
            'color' => 'required|string|max:7',
            'activo' => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es requerido.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
            'color.required' => 'El color es requerido.',
            'activo.required' => 'El estado es requerido.',
        ]);

        \DB::beginTransaction();
        try {
            $old = $categoria->toArray();
            $data = $request->all();

            // Verificar si hubo cambios
            $hasChanges = false;
            $fieldsToCheck = ['nombre', 'descripcion', 'color', 'activo'];
            foreach ($fieldsToCheck as $field) {
                if (array_key_exists($field, $data) && $data[$field] != $categoria->$field) {
                    $hasChanges = true;
                    break;
                }
            }

            if (!$hasChanges) {
                \DB::rollBack();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'No se realizaron cambios en la categoría.',
                        'redirect' => route('categorias.index')
                    ]);
                }

                return redirect()->route('categorias.index')
                    ->with('info', 'No se realizaron cambios en la categoría.');
            }

            $data['updated_by'] = auth()->id();
            $categoria->update($data);

            $this->registrarAuditoria('update', $categoria, $old, $data, 'Categoría actualizada');

            \DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría actualizada exitosamente.',
                    'redirect' => route('categorias.index')
                ]);
            }

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría actualizada exitosamente.');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error al actualizar categoría: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la categoría.'
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Error al actualizar la categoría.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            $categoria = Categoria::findOrFail($id);

            // Validación manual
            if (empty($request->password)) {
                \DB::rollBack();
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['password' => ['La contraseña es requerida.']]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['password' => 'La contraseña es requerida.'])
                    ->withInput()
                    ->with('modal', 'eliminar-'.$id);
            }

            if (empty($request->observacion)) {
                \DB::rollBack();
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['observacion' => ['La observación es requerida.']]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['observacion' => 'La observación es requerida.'])
                    ->withInput()
                    ->with('modal', 'eliminar-'.$id);
            }

            if (!\Hash::check($request->password, auth()->user()->password)) {
                \DB::rollBack();
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['password' => ['Contraseña incorrecta.']]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['password' => 'Contraseña incorrecta.'])
                    ->withInput()
                    ->with('modal', 'eliminar-'.$id);
            }

            // Validar si tiene productos asociados
            if ($categoria->productos()->count() > 0) {
                \DB::rollBack();
                $msg = 'No se puede eliminar la categoría porque tiene ' . $categoria->productos()->count() . ' producto(s) asociado(s).';
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['general' => [$msg]]
                    ], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            $old = $categoria->toArray();
            $categoria->delete();

            $this->registrarAuditoria('delete', $categoria, $old, null, 'Categoría eliminada (soft)', $request->observacion);

            \DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría eliminada temporalmente.'
                ]);
            }

            return redirect()->route('categorias.index')->with('success', 'Categoría eliminada temporalmente.');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error al eliminar categoría: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['general' => ['Error al eliminar la categoría.']]
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar la categoría.');
        }
    }

    /**
     * Restore a soft deleted resource.
     */
    public function restore(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            $request->validate([
                'password' => 'required|string',
                'observacion' => 'required|string',
            ], [
                'password.required' => 'La contraseña es requerida.',
                'observacion.required' => 'La observación es requerida.',
            ]);

            if (!\Hash::check($request->password, auth()->user()->password)) {
                \DB::rollBack();
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['password' => ['Contraseña incorrecta.']]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['password' => 'Contraseña incorrecta.'])
                    ->withInput()
                    ->with('modal', 'restaurar-'.$id);
            }

            $categoria = Categoria::onlyTrashed()->findOrFail($id);
            $categoria->restore();

            $this->registrarAuditoria('restore', $categoria, null, $categoria->toArray(), 'Categoría restaurada', $request->observacion);

            \DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría restaurada.'
                ]);
            }

            return redirect()->route('categorias.index', ['eliminados' => 1])->with('success', 'Categoría restaurada.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $ve->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('modal', 'restaurar-'.$id);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error al restaurar categoría: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['general' => ['Error al restaurar la categoría.']]
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al restaurar la categoría.');
        }
    }

    /**
     * Permanently delete a soft deleted resource.
     */
    public function forceDelete(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            $request->validate([
                'password' => 'required|string',
                'observacion' => 'required|string',
            ], [
                'password.required' => 'La contraseña es requerida.',
                'observacion.required' => 'La observación es requerida.',
            ]);

            if (!\Hash::check($request->password, auth()->user()->password)) {
                \DB::rollBack();
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['password' => ['Contraseña incorrecta.']]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['password' => 'Contraseña incorrecta.'])
                    ->withInput()
                    ->with('modal', 'borrar-definitivo-'.$id);
            }

            $categoria = Categoria::onlyTrashed()->findOrFail($id);

            // Validar si tiene productos asociados
            if ($categoria->productos()->count() > 0) {
                \DB::rollBack();
                $msg = 'No se puede eliminar permanentemente la categoría porque tiene productos asociados.';
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['general' => [$msg]]
                    ], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            $old = $categoria->toArray();
            $categoria->forceDelete();

            $this->registrarAuditoria('forceDelete', $categoria, $old, null, 'Categoría eliminada permanentemente', $request->observacion);

            \DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría eliminada permanentemente.'
                ]);
            }

            return redirect()->route('categorias.index', ['eliminados' => 1])->with('success', 'Categoría eliminada permanentemente.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $ve->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('modal', 'borrar-definitivo-'.$id);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error al eliminar permanentemente categoría: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['general' => ['Error al eliminar permanentemente la categoría.']]
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar permanentemente la categoría.');
        }
    }

    /**
     * Registrar auditoría
     */
    private function registrarAuditoria($accion, $modelo, $old, $new, $descripcion, $observacion = null)
    {
        try {
            Auditoria::create([
                'user_id' => Auth::id(),
                'action' => $accion,
                'model_type' => get_class($modelo),
                'model_id' => $modelo->id,
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
                'description' => $descripcion,
                'observacion' => $observacion,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría: ' . $e->getMessage());
        }
    }
}
