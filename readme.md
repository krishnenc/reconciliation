## Reconciliation trial project

Two files with transactions are provided. Transactions are compared for similarity. For transactions that cannot be matched exactly, suggestions are proposed 
and a score is assigned to each match found.
At the moment only rows which are completely similar are considered an exact match.
Maybe suggestions with a score higher than 90% should probably be considered an exact match.

Bulk of the functionality are in these files:

* App\Http\Controllers\ReconciliationController
* App\ReconciliationUtils

Test cases are in :

tests/ReconciliationTest.php

How to run:

* on cmd line run : php artisan serve , then go to http://localhost:8000

How to run tests:

* run phpunit on cmd line

TODO:

* Validate that the files are of mime type CSV
* Validate that the sum of the weight criteria on the form is always equal to 1



