<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class ProductoController extends Controller
{
    public function index()
    {
        try {
            $productos = Producto::with('marca','categoria')->get(); //si no se maneja muchos datos
            //$productos = Producto::all(); //si se maneja muchos datos
            return ApiResponse::success("Lista de productos", 200, $productos);
        } catch(Exception $e){
            return ApiResponse::error('Error al obtener las listas: ' .$e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'nombre' => 'required|unique:productos',
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id'
            ]);
            $producto = Producto::create($request->all());
            return ApiResponse::success("Producto creado exitosamente", 201, $producto);
        }catch(ValidationException $errors){
            $errors = $errors->validator->errors()->toArray();
            if(isset($errors['categoria_id'])){
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }
            if(isset($errors['marca_id'])){
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }
            return ApiResponse::error("Error al crear el producto: ",422, $errors);
        }
    }

    public function show($id)
    {
        try{
            $producto = Producto::with('marca','categoria')->findOrFail($id);
            return ApiResponse::success("Producto obtenido exitosamente", 200, $producto);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error('Producto no encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $producto = Producto::findOrFail($id);
            $request->validate([
                'nombre' => 'required|unique:productos,nombre,'.$producto->id,
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id'
            ]);
            $producto->update($request->all());
            return ApiResponse::success("Producto actualizado exitosamente", 200, $producto);
        }catch(ValidationException $errors){
            $errors = $errors->validator->errors()->toArray();
            if(isset($errors['categoria_id'])){
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }
            if(isset($errors['marca_id'])){
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }
            return ApiResponse::error("Error al actualizar el producto: ",422, $errors);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error('Producto no encontrado', 404);
        }
    }

    public function destroy($id)
    {
        try{
            $producto = Producto::findOrFail($id);
            $producto->delete();
            return ApiResponse::success("Producto eliminado exitosamente", 200);
        }catch(ModelNotFoundException $e){
            return ApiResponse::error('Producto no encontrado', 404);
        }
    }
}
