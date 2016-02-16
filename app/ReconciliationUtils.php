<?php

namespace App;

/*
    Class: ReconciliationUtils

    Utilities for transaction reconciliation
*/
class ReconciliationUtils 
{

    // In Minutes the interval within which two transaction dates are considered similar
    const MAX_DIFFERENCE_TRANSACTION_DATE = 60;
    // The minimum score that the weighted average of the criteria has to match to be considered a valid suggestion
    const MINIMUM_CONFIDENCE_SCORE = 50;

    //TODO ; If a match scores higher than this number , maybe consider it an exact match
    const EXACT_MATCH_CONFIDENCE_SCORE = 90;

    //List of weight per transaction criteria to evaluate transaction similarity
    const TRANSACTION_AMOUNT_WEIGHT = 0.4;
    const TRANSACTION_WALLET_REFERENCE_WEIGHT = 0.3;
    const TRANSACTION_DATE_WEIGHT = 0.15;
    const TRANSACTION_NARRATIVE_WEIGHT = 0.1;
    const TRANSACTION_ID_WEIGHT = 0.05;

    public static $maxdiffdate;
    public static $minconfidencescore;
    public static $amountweight;
    public static $walletrefweight;
    public static $dateweight;
    public static $idweight;
    public static $narrativeweight;
    
    /**
     * Compares two CSV files and determines for the transactions
     * the exact matches and subsequently closest matches for the ones not found in the other file
     * The process is repeated twice for the two files and the combined results are computed
     *
     * @param  String  $file1Path
     * @param  String  $file2Path
     * @return Array $comparisonResult
    */
    public static function compareFile($file1Path,$file2Path){

        $file1 = self::csv_to_array($file1Path);
        $file2 = self::csv_to_array($file2Path);

        $matches = [];
        $non_matches = [];
        $count = 0;
        
        $countMatchReports = 0;
        for ($i = 0; $i < count($file1); ++$i) {
            $file1Transaction = $file1[$i];
            $matchFound = false;
            //Start searching from the current index of the first file, since the data is mostly ordered
            //in the files
            for ($j=$i; $j < count($file2); $j++) { 
                $result=array_diff_assoc($file1Transaction,$file2[$j]);
                if(count($result) == 0){
                    if (!isset($matches[$i]))
                        $matches[$i] = $file1Transaction;

                    $matches[$i]['matched'] = 1;
                    $file2[$j]['matched'] = 1;
                    //$matches[$i]['matches'][] = $file2[$j];
                    $matchFound = true;
                    $countMatchReports++;
                    break;
                }
            }
            if (!$matchFound){
                //Start searching from top of file up to last index we searched to or the full file 
                //if the index has already exceeded the allowance
                $index = ($i > count($file2)) ? count($file2) : $i;
                for ($j=0; $j < $index; $j++) { 
                    $result=array_diff_assoc($file1Transaction,$file2[$j]);
                    if(count($result) == 0){
                        if (!isset($matches[$i]))
                            $matches[$i] = $file1Transaction;

                        $matches[$i]['matched'] = 1;
                        $file2[$j]['matched'] = 1;
                        //$matches[$i]['matches'][] = $file2[$j];
                        $matchFound = true;
                        $countMatchReports++;
                        break;
                    }
                }
            }
            if (!$matchFound){
                $non_matches[$i+2] = $file1Transaction;
            }
        }

        foreach ($non_matches as $key => $value) {
            $non_matches[$key] = self::findSuggestionForNonMatch($value,$file2);
        }

        $comparisonResult['TOTAL'] = count($file1);
        $comparisonResult['MATCHED'] = $countMatchReports;
        $comparisonResult['UNMATCHED_COUNT'] = count($non_matches);
        $comparisonResult['UNMATCHED_TRANSACTIIONS'] = $non_matches;
        //Try to find suggestions for transactions that did not match exactly
        return $comparisonResult;
    }

    /**
     *
     * Function to determine if there are similar transactions in the other file
     */
    private static function findSuggestionForNonMatch($nonMatchedTransaction , $file2)
    {
        for ($i = 0; $i < count($file2); ++$i) {
            if(!isset($file2[$i]['matched']) || $file2[$i]['matched'] != 1){
                
                //Calculate how similar the transaction narrative is betweed the two records
                $transactionNarrative = self::calculateWeightForTransactionNarrative($nonMatchedTransaction['TransactionNarrative']
                                              ,$file2[$i]['TransactionNarrative']);
                
                //Check if the wallet reference matches
                $walletRefWeight = 0;
                if ($nonMatchedTransaction['WalletReference'] == $file2[$i]['WalletReference']){
                    $walletRefWeight = 100;
                }

                //Check if the transaction id matches
                $transactionIdWeight = 0;
                if ($nonMatchedTransaction['TransactionID'] == $file2[$i]['TransactionID']){
                    $transactionIdWeight = 100;
                }

                //Check that the interval is less than MAX_DIFFERENCE_TRANSACTION_DATE
                // If it is then the interval can be taken into consideration as a rating criteria
                $nonMatchTransDate = strtotime($nonMatchedTransaction['TransactionDate']);
                $otherFileTransDate = strtotime($file2[$i]['TransactionDate']);
                $interval = round(abs($nonMatchTransDate - $otherFileTransDate) / 60);

                if ($interval < self::$maxdiffdate)
                    $transactionDateInterval = ((self::$maxdiffdate - $interval) / self::$maxdiffdate) * 100;
                else
                    $transactionDateInterval = 0;    
                

                //Check if we can match the amount exactly
                $transactionAmount = 0;
                if ($nonMatchedTransaction['TransactionAmount'] == $file2[$i]['TransactionAmount']){
                    $transactionAmount = 100;
                }


                //Calculate the weighted average of the different criteria score/weight calculated above
                //Only scores higher than the MINIMUM_CONFIDENCE_SCORE will be considered to be valid as a suggestion
                $weightAvg = ($transactionAmount * self::$amountweight) 
                                + ($transactionDateInterval * self::$dateweight)
                                + ($walletRefWeight * self::$walletrefweight)
                                + ($transactionIdWeight * self::$idweight)
                                + ($transactionNarrative * self::$narrativeweight);


                if ($weightAvg > self::$minconfidencescore){
                   //The transaction can be considered as a suggestion
                    $file2[$i]['SCORE'] = $weightAvg.'%';
                    $nonMatchedTransaction['SUGGESTIONS'][] = $file2[$i];
                 }
            }
        }
        return $nonMatchedTransaction;
    }

    /*
        Break up the narrative of both transactions into arrays and for each word in
        the transaction we are trying to match, determine if the word is similar to a word in the other narrative
        Calculate the mean of the similarity as the weight to assign to the narrative
    */
    private static function calculateWeightForTransactionNarrative($nonmatchNarrative, $otherFileNarrative)
    {
        $wordPercentage = array();
        $nonmatchNarrative = strtoupper($nonmatchNarrative);
        $nonmatchNarrative = preg_replace('/\s+/', ',', $nonmatchNarrative);
        $_input_pieces = explode(",", $nonmatchNarrative);

        $otherFileNarrative = strtoupper($otherFileNarrative);
        $otherFileNarrative = preg_replace('/\s+/', ',', $otherFileNarrative);
        $pieces = explode(",", $otherFileNarrative);

        for ($i=0; $i < count($_input_pieces); $i++) { 
            $percent = null;
            $found = self::closest_word($_input_pieces[$i], $pieces, $percent);
            $roundedPercent = round($percent * 100, 2);
            if ($roundedPercent > 0)
              $wordPercentage[] = round($percent * 100, 2);
        }
        if (count($wordPercentage) > 0)
        {
          $mean = array_sum($wordPercentage)/count($wordPercentage);
          return $mean;
        }
        else
            return 0;
    }

    /*
    *
        Converts a CSV file into an associative array, using the CSV header as keys for each transaction record
    */
    private static function csv_to_array($filename='', $delimiter=',')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;
        
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                //print_r($header);
                if(!$header)
                {
                    $header = $row;
                    $header[8] = 'Weight';
                }
                else{
                    $row[3] = strtoupper($row[3]);
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }

    /*
        Returns the closest word from an array, and assigns the percentage to a referenced variable:
    */
    private static function closest_word($input, $words, &$percent = null) {
        $shortest = -1;
        foreach ($words as $word) {
          $lev = levenshtein($input, $word);
          if ($lev == 0) {
            $closest = $word;
            $shortest = 0;
            break;
          }
          if ($lev <= $shortest || $shortest < 0) {
            $closest  = $word;
            $shortest = $lev;
          }
        }
        $percent = 1 - levenshtein($input, $closest) / max(strlen($input), strlen($closest));
        return $closest;
    } 

}
