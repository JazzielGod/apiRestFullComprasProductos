<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
class CategoriaController extends Controller
{
    public function index()
    {
        try{
            //todo el codigo inicial 
            //todo funciona bien
            $categorias =  Categoria::all();
            return ApiResponse::success('Lista de Categorias', 200, $categorias);
            //throw new Exception('Error al obtener las categorias');
        } catch(Exception $e){
            //error en el codigo
            return ApiResponse::error('Error al obtener las listas: ' .$e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'nombre' => 'required|unique:categorias',
            ]);
            $categoria = Categoria::create($request->all());
            return ApiResponse::success('Categoria creada con exito', 201, $categoria);
        }catch(ValidationException $e){
            return ApiResponse::error('Error al crear la categoria: ' .$e->getMessage(), 422);
        }
    }

    public function show($id)
    {
        try{
            $categoria = Categoria::findOrFail($id);
            return ApiResponse::success('Categoria obtenida exitosamente', 200, $categoria);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error('Categoria no encontrada', 404);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $categoria = Categoria::findOrFail($id);//nombre de la categoria que se va a actualizar
            $request->validate([
                'nombre' => ['required', Rule::unique('categorias')->ignore($categoria)],
            ]);
            $categoria->update($request->all());
            return ApiResponse::success('Categoria actualizada exitosamente', 200, $categoria);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error('Categoria no encontrada', 404);
        }catch(Exception $e){
            return ApiResponse::error('Error:'.$e->getMessage(), 422);
        }
    }

    public function destroy($id)
    {
        try{
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();
            return ApiResponse::success('Categoria eliminada exitosamente', 200);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error('Categoria no encontrada', 404);
        }
    }

    public function productosPorCategoria($id)
    {
        try {
            $categoria = Categoria::with('productos')->findOrFail($id);
            return ApiResponse::success('Categoria y lista de productos', 200, $categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoria no encontrada', 404);
        }
    }
}
