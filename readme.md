## Reconciliation trial project

Two files are provided. Rows are compared for similarity and a score is assigned to each match found.
At the moment only rows which are completely similar are considered an exact match.
Suggestions with a score higher than 90% should probably be considered an exact match.

Bulk of function is in these files:

App\Http\Controllers\ReconciliationController
App\ReconciliationUtils



