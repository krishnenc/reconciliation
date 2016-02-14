<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\ReconciliationUtils;

class ReconciliationTest extends TestCase
{

    /*
        Init criteria weight
    */
    function __construct() {
       ReconciliationUtils::$maxdiffdate = 60;
       ReconciliationUtils::$minconfidencescore = 50;
       ReconciliationUtils::$amountweight = 0.4;
       ReconciliationUtils::$walletrefweight = 0.3;
       ReconciliationUtils::$dateweight = 0.15;
       ReconciliationUtils::$idweight = 0.05;
       ReconciliationUtils::$narrativeweight = 0.1;
    }


    /**
     * Test that we can find an exact match for a transaction
     *
     * @return void
     */
    public function testExactMatch()
    {
        $uploadPath = public_path().'/uploads';
        $result = ReconciliationUtils::compareFile($uploadPath.'/test1File1.csv',$uploadPath.'/test1File2.csv');
        $this->assertEquals(1, $result['MATCHED']);
    }

    /**
     * Test that we can find a suggestion for a transaction
     *
     * @return void
     */
    public function testFindSuggestion()
    {
        $uploadPath = public_path().'/uploads';
        $result = ReconciliationUtils::compareFile($uploadPath.'/test2File1.csv',$uploadPath.'/test2File2.csv');
        $this->assertEquals(1, count($result['UNMATCHED_TRANSACTIIONS'][2]['SUGGESTIONS']));
    }

    /**
     * Test that we cannot find a suggestion for a transaction
     *
     * @return void
     */
    public function testCannotFindSuggestion()
    {
        $uploadPath = public_path().'/uploads';
        $result = ReconciliationUtils::compareFile($uploadPath.'/test3File1.csv',$uploadPath.'/test3File2.csv');
        $this->assertEquals(0, isset($result['UNMATCHED_TRANSACTIIONS'][2]['SUGGESTIONS']));
    }

}
