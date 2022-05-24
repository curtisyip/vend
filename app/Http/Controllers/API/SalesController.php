<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use Validator;

class SalesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sales(Request $request)
    {
        $inputs = $request->json()->all();
        $sales = $inputs['sales'];
        if ($request->has('discount')) {
            $discount = $inputs['discount'];
        }
        else {
            $discount = 0;
        }
        $sales_total = 0;
        $total_qty = array_sum(array_column($sales, 'quantity'));

        if (count($sales) > 0) {
            foreach ($sales as $key => $s) {
                $validator = Validator::make($s, [
                    'product_id' => 'required',
                    'quantity' => 'required'
                ]);
            
                if($validator->fails()){
                    return $this->sendError('Validation Error.', $validator->errors());       
                }
                
                $product = Product::findOrFail($s['product_id']);

                $total = $product->price * $s['quantity'];

                $sales[$key]['total'] = $total;

                $sales_total = $sales_total + $total;
                
                $sales[$key]['discount'] = $s['quantity'] / $total_qty * $discount;
            }
        }

        $data = [
            "sales" => $sales,
            "discount" => $discount,
            "sales_total" => $sales_total - $discount,
        ];

        return $this->sendResponse($data, 'Sales created successfully');
    }
}
