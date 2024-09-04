<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    //

    public function fetch(Request $request) {
        $id = $request->id;
        $limit = $request->limit;
        $status = $request->status;

        if($id) {
            $transaksi = Transaction::with('items.product')->find($id);

            if($transaksi) {
                return ResponseFormatter::success($transaksi, "Data Transaksi Berhasil Diambil");
            } else {
                return ResponseFormatter::error(null, "Data Transaksi Tidak Ditemukan", 404);
            }
        } 

        $transaksi = Transaction::with('items.product')->where("users_id", Auth::user()->id);

        if($status) {
            $transaksi->where('status', $status);
        }
        if($transaksi) {
            return ResponseFormatter::success($transaksi->paginate($limit), "Data Transasksi Berhasil Diambil");
        } else {
            return ResponseFormatter::error(null, "Data Transaksi Tidak Ditemukan", 404);
        }
    }

    public function checkout(Request $request) {
        try {
            $request->validate([
                "address" => 'required|min:125',
                "items" => 'required|array',
                "items.*.id" => 'exist:products.id',
                "total_price" => 'required',
                "shipping_price" => 'required',
                "status" => 'required|in:PENDING,SUCCESS,CANCELLED, FAILED, SHIPPING, SHIPED'
            ]);
    
            $users_id = Auth::user()->id;
            $address = $request->address;
            $items= $request->items;
            $total_price = $request->total_price;
            $shipping_price = $request->shipping_price;
            $status = $request->status;
            $transaksi = Transaction::create([
                'users_id' =>  $users_id,
                'address' => $address,
                'total_price' => $total_price,
                'shipping_price' => $shipping_price,
                'status' => $status,
            ]);
            
            foreach ($items as $product) {
                TransactionItem::create([
                    'users_id' => $users_id,
                    'products_id' => $product->id,
                    'transactions_id' => $transaksi->id,
                    'quantity' => $product->quantity,
                ]);
            }
    
            return ResponseFormatter::success($transaksi->load('items.product'), 'Transaksi berhasil');
            
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went error',
                    'error' => $e->getMessage(),
                ],
                'Authentication Failed',
                500
            );
        }
       
    }
}
