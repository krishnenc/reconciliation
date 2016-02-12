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

    public function index(Request $request) {
    	$result = is_null($request->input('results')) ? false : true;
    	$data = array();
    	//var_dump($request->input('results'));
    	if ($request->input('results') == NULL)
    		$data['RESULTS'] = 0;
    	else 
    	{
         	$data['RESULTS'] = 1;
         	//Get the comparison results and push it back to the view
         	$data['FILE1_DATA'] = $request->session()->get('FILE1_DATA');
         	$data['FILE2_DATA'] = $request->session()->get('FILE2_DATA');
    	}
    	return view('reconciliation', compact('data'));
    }

    public function compare(Request $request) {
    	//required|mimes:csv
    	$this->validate($request, [
        	'file1' => 'required',
	        'file2' => 'required',
	    ]);
    	
		$file1Name = $request->file('file1')->getClientOriginalName();
		$uploadPath = public_path().'\Uploads';
		$request->file('file1')->move($uploadPath,$file1Name);
	
		$file2Name = $request->file('file2')->getClientOriginalName();
    	$request->file('file2')->move($uploadPath,$file2Name);
	
    	$file1Path = $uploadPath.'/'.$file1Name;
    	$file2Path = $uploadPath.'/'.$file2Name;

        $request->session()->flush();
        
    	$compareFile1ToFile2Result = ReconciliationUtils::compareFile($file1Path,$file2Path);
    	$compareFile1ToFile2Result['FILE1_NAME'] = $file1Name;
    	$request->session()->put('FILE1_DATA', $compareFile1ToFile2Result);

    	$compareFile2ToFile1Result = ReconciliationUtils::compareFile($file2Path,$file1Path);
    	$compareFile2ToFile1Result['FILE2_NAME'] = $file2Name;
    	$request->session()->put('FILE2_DATA', $compareFile2ToFile1Result);

		return redirect('/reconciliation?results=true')->withSuccess("Comparison done!");
    }

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
