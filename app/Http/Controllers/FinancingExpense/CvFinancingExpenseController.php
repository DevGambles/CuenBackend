<?php

namespace App\Http\Controllers\FinancingExpense;

use App\CvFinancingExpense;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CvFinancingExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expenseTotal = 0;
        $collExpenses = CvFinancingExpense::all();

        foreach ($collExpenses as $collExpens) {
            $expenseTotal += $collExpens->value;
        }
        return response()->json(
            [
                'data' => $collExpenses,
                'totalExpense' => $expenseTotal
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return CvFinancingExpense::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CvFinancingExpense  $cvFinancingExpense
     * @return \Illuminate\Http\Response
     */
    public function show(CvFinancingExpense $cvFinancingExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CvFinancingExpense  $cvFinancingExpense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CvFinancingExpense $financingExpense)
    {

        $request['balance']= $request->value - $financingExpense->payed;
        if ( (int) $request['balance'] < 0){
            return response()->json(['message' => 'Ha ocurrido un error al editar el registro', 'code' => 400], 400);
        }
        if ($financingExpense->update($request->all())){
            return response()->json(['data' => $financingExpense, 'code' => 200], 200);
        } else{
            return response()->json(['message' => 'Ha ocurrido un error al editar el registro', 'code' => 500], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CvFinancingExpense  $cvFinancingExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(CvFinancingExpense $cvFinancingExpense)
    {
        //
    }
}
