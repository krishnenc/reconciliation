<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\ReconciliationUtils;

/*
    Class: ReconciliationController

    Handles requests coming from the reconciliation screen
*/
class ReconciliationController extends Controller
{

    /*
        Default view controller
        When redirected from compare , it displays the comparison table
    */
    public function index(Request $request) {
    	$result = is_null($request->input('results')) ? false : true;
    	$data = array();
    	if ($request->input('results') == NULL)
    		$data['RESULTS'] = 0;
    	else 
    	{
         	$data['RESULTS'] = 1;
         	//Get the comparison results and push it back to the view
         	$data['FILE1_DATA'] = $request->session()->get('FILE1_DATA');
         	$data['FILE2_DATA'] = $request->session()->get('FILE2_DATA');
    	}
        $data['MINIMUM_CONFIDENCE_SCORE'] = ReconciliationUtils::MINIMUM_CONFIDENCE_SCORE;
        $data['MAX_DIFFERENCE_TRANSACTION_DATE'] = ReconciliationUtils::MAX_DIFFERENCE_TRANSACTION_DATE;

        //Weight of comparison criteria
        $data['TRANSACTION_AMOUNT_WEIGHT'] = ReconciliationUtils::TRANSACTION_AMOUNT_WEIGHT;
        $data['TRANSACTION_WALLET_REFERENCE_WEIGHT'] = ReconciliationUtils::TRANSACTION_WALLET_REFERENCE_WEIGHT;
        $data['TRANSACTION_DATE_WEIGHT'] = ReconciliationUtils::TRANSACTION_DATE_WEIGHT;
        $data['TRANSACTION_NARRATIVE_WEIGHT'] = ReconciliationUtils::TRANSACTION_NARRATIVE_WEIGHT;
         $data['TRANSACTION_ID_WEIGHT'] = ReconciliationUtils::TRANSACTION_ID_WEIGHT;

    	return view('reconciliation', compact('data'));
    }

    /*
        Compares two files uploaded from the reconcialition screen
        The comparison data is stored in the user session
    */
    public function compare(Request $request) {
    	//required|mimes:csv
    	$this->validate($request, [
        	'file1' => 'required',
	        'file2' => 'required',
            'confidencemin' => 'required|max:50|numeric|min:10',
            'maxdifftransdate' => 'required|numeric|max:300|min:10',
            'transamountweight' => 'required|numeric|max:1|min:0',
            'transdateweight' => 'required|numeric|max:1|min:0',
            'transnarrativeweight' => 'required|numeric|max:1|min:0',
            'transwalletrefweight' => 'required|numeric|max:1|min:0',
            'transidweight' => 'required|numeric|max:1|min:0'
	    ]);
    	
		$file1Name = $request->file('file1')->getClientOriginalName();
		$uploadPath = public_path().'/uploads';
		$request->file('file1')->move($uploadPath,$file1Name);
	
		$file2Name = $request->file('file2')->getClientOriginalName();
    	$request->file('file2')->move($uploadPath,$file2Name);
	
    	$file1Path = $uploadPath.'/'.$file1Name;
    	$file2Path = $uploadPath.'/'.$file2Name;

        $request->session()->flush();

        //Init the weight of each criteria
        ReconciliationUtils::$maxdiffdate = $request->input('maxdifftransdate');
        ReconciliationUtils::$minconfidencescore = $request->input('confidencemin');;
        ReconciliationUtils::$amountweight = $request->input('transamountweight');;
        ReconciliationUtils::$walletrefweight = $request->input('transwalletrefweight');;
        ReconciliationUtils::$dateweight = $request->input('transdateweight');;
        ReconciliationUtils::$idweight = $request->input('transidweight');;
        ReconciliationUtils::$narrativeweight = $request->input('transnarrativeweight');;
        
    	$compareFile1ToFile2Result = ReconciliationUtils::compareFile($file1Path,$file2Path);
    	$compareFile1ToFile2Result['FILE1_NAME'] = $file1Name;
    	$request->session()->put('FILE1_DATA', $compareFile1ToFile2Result);

    	$compareFile2ToFile1Result = ReconciliationUtils::compareFile($file2Path,$file1Path);
    	$compareFile2ToFile1Result['FILE2_NAME'] = $file2Name;
    	$request->session()->put('FILE2_DATA', $compareFile2ToFile1Result);

		return redirect('/reconciliation?results=true')->withSuccess("Comparison done!");
    }

    /*
    *
        Gets transactions that are unmatched for the file specified with an index
    */
    public function unmatched(Request $request) {
        $unmatchedFileIndex = $request->input('index');
        $data = $request->session()->get('FILE'.$unmatchedFileIndex.'_DATA');
        $table= array();
        foreach ($data['UNMATCHED_TRANSACTIIONS'] as $key => $value) {
            $data= array();
            $data[] = $value['TransactionDate'];
            $data[] = $value['TransactionAmount'];
            $data[] = $value['TransactionNarrative'];
            $data[] = $value['TransactionID'];
            $data[] = $value['WalletReference'];
            if (isset($value['SUGGESTIONS'])){
                $data[] = count($value['SUGGESTIONS']);
            }
            else{
                $data[] = 0;
            }            
            $data[] = $key;
            $table['data'][] = $data;
        }
    	return response()->json($table);
    }

    /*
        Gets the suggestions for an unmatched transaction
    */
    public function suggestions(Request $request) {
        $unmatchedFileIndex = $request->input('index');
        $data = $request->session()->get('FILE'.$unmatchedFileIndex.'_DATA');

        $unmatchedLineNo = $request->input('line');
        $unmatchedSuggestions = $data['UNMATCHED_TRANSACTIIONS'][$unmatchedLineNo]['SUGGESTIONS'];

        $table= array();
        foreach ($unmatchedSuggestions as $key => $value) {
            $data= array();
            $data[] = $value['TransactionDate'];
            $data[] = $value['TransactionAmount'];
            $data[] = $value['TransactionNarrative'];
            $data[] = $value['TransactionID'];
            $data[] = $value['WalletReference'];
            $data[] = $value['SCORE'];
            $table['data'][] = $data;
        }
        return response()->json($table);
    }

}
