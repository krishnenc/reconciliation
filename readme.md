## Reconciliation trial project

Two files are provided. Rows are compared for similarity. For transactions that cannot be matched exactly, suggestions are proposed and a score is assigned to each match found.
At the moment only rows which are completely similar are considered an exact match.
Suggestions with a score higher than 90% should probably be considered an exact match.

Bulk of the functionality are in these files:

* App\Http\Controllers\ReconciliationController
* App\ReconciliationUtils



