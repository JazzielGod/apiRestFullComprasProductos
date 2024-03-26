<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Producto;
use App\Models\Compra;
use Illuminate\Database\QueryException;
class CompraController extends Controller
{
    public function index(){
        # code
    }

    public function store(Request $request)
    {
        try {
            $productos = $request->input('productos');
            //validar los productos
            if(empty($productos)){
                return ApiResponse::error('No se proporcionaron productos', 400);
            }
            //validar la lista de productos
            $validator = Validator::make($request->all(),[
                'productos' => 'required|array',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1'
            ]);
            if($validator->fails()){
                return ApiResponse::error("Datos invalidos de la lista de productos", 400, $validator->errors());
            }
            //validar productos duplicados
            $productoIds = array_column($productos, 'producto_id');
            if(count($productoIds) !== count(array_unique($productoIds))){
                return ApiResponse::error("No se permiten productos duplicados para la compra", 400);
            }
            $totalPagar=0;
            $subtotal=0;
            $compraItems=[];
            //iteracion de los productos  
            foreach($productos as $producto){
                $productoB = Producto::find($producto['producto_id']);
                /*if(!$productoB){
                    return ApiResponse::error("El producto con el id {$producto['producto_id']} no existe", 404);
                }*/
                //validar la cantidad disponible de los productos
                if($productoB->cantidad_disponible < $producto['cantidad']){
                    return ApiResponse::error("El producto no tiene suficiente cantidad disponible", 404);
                }
                //actualizacion de la cantidad disponible de cada producto
                $productoB->cantidad_disponible -= $producto['cantidad'];
                $productoB->save();
                //calculo de los importes
                $subtotal = $productoB->precio * $producto['cantidad'];
                $totalPagar += $subtotal;
                //items de la compra
                $compraItems[] = [
                    'producto_id' => $productoB->id,
                    'precio' => $productoB->precio,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $subtotal
                ];
            }
            //Registro en la tabla de compras
            $compra = Compra::create([
                'subtotal' => $totalPagar,
                'total' => $totalPagar
            ]);
            //Asociar los prodcutos a la compra con sus cantidades y sus subtotales
            $compra->productos()->attach($compraItems);
            return ApiResponse::success('Compra realizada exitosamente',201,$compra);
        } catch (QueryException $e) {
            //error de consulta en la base de datos
            return ApiResponse::error("Error en la consulta en la BD",500);
        }catch(Exception $e){
            //error en el servidor
            return ApiResponse::error("Error inesperado",500, $e->getMessage());

        }  
    }

    public function show($id)
    {
        # code
    }
}
