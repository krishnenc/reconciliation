@extends('admin_template')

@section('content')
    <div class='row'>
        <div class='col-md-6'>
            <!-- Box -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Select files to compare</h3>
                </div>
                <div class="box-body">
                    {!! Form::open(
                        array(
                            'route' => 'compare', 
                            'class' => 'form-group', 
                            'novalidate' => 'novalidate', 
                            'files' => true)) !!}
                    <div class="form-group">
                        {!! Form::label('Select File 1') !!}
                        {!! Form::file('file1', null) !!}
                    </div>
                   <div class="form-group">
                        {!! Form::label('Select File 2') !!}
                        {!! Form::file('file2', null) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::submit('Compare!') !!}
                    </div>
                    {!! Form::close() !!}
                    @include('partials.errors')
                    @include('partials.success')

                </div><!-- /.box-footer-->
            </div><!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
    <div class='row' id="results">
        <div class='col-md-8'>
            <!-- Box -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Comparison results</h3>
                </div>
                <div class="box-body">
                    <div class='col-md-4'>
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title" id="file1Name">{{{ isset($data['FILE1_DATA']) ? $data['FILE1_DATA']['FILE1_NAME'] : 'Default' }}}</h3>
                            </div>
                             <div class="box-body">
                                <dl class="dl-horizontal">
                                  <dt>Total Records</dt>
                                  <dd id="file1Total">{{{  isset($data['FILE1_DATA']) ? $data['FILE1_DATA']['TOTAL'] : 'Default' }}}</dd>
                                </dl>
                                <dl class="dl-horizontal">
                                  <dt>Matching Records</dt>
                                  <dd id="file1Matching">{{{  isset($data['FILE1_DATA']) ? $data['FILE1_DATA']['MATCHED'] : 'Default' }}}</dd>
                                </dl>
                                <dl class="dl-horizontal">
                                  <dt>Unmatched Records</dt>
                                  <dd id="file1Unmatched">{{{  isset($data['FILE1_DATA']) ? $data['FILE1_DATA']['UNMATCHED_COUNT'] : 'Default' }}}</dd>
                                </dl>
                             </div>
                        </div>
                    </div>
                    <div class='col-md-4'>
                       <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title" id="file2Name">{{{  isset($data['FILE2_DATA']) ? $data['FILE2_DATA']['FILE2_NAME'] : 'Default' }}}</h3>
                            </div>
                            <div class="box-body">
                                <dl class="dl-horizontal">
                                  <dt>Total Records</dt>
                                  <dd id="file2Total">{{{  isset($data['FILE2_DATA']) ? $data['FILE2_DATA']['TOTAL'] : 'Default' }}}</dd>
                                </dl>
                                <dl class="dl-horizontal">
                                  <dt>Matching Records</dt>
                                  <dd id="file2Matching">{{{  isset($data['FILE2_DATA']) ? $data['FILE2_DATA']['MATCHED'] : 'Default' }}}</dd>
                                </dl>
                                <dl class="dl-horizontal">
                                  <dt>Unmatched Records</dt>
                                  <dd id="file2Unmatched">{{{  isset($data['FILE2_DATA']) ? $data['FILE2_DATA']['UNMATCHED_COUNT'] : 'Default' }}}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-2 clearfix'>
                        <span class="input-group-btn">
                            <button type="button" id="unmatched" class="btn bg-blue btn-flat margin">Unmatched records</button>
                        </span>
                    </div>
                  </div><!-- /.box-footer-->
            </div><!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
    <div class='row' id="reports">
        <div class='col-md-8'>
            <!-- Box -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Unmatched report</h3>
                </div>
                <div class="box-body">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                      <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab">{{{ isset($data['FILE1_DATA']) ? $data['FILE1_DATA']['FILE1_NAME'] : 'Default' }}}</a></li>
                        <li><a href="#tab_2" data-toggle="tab">{{{ isset($data['FILE2_DATA']) ? $data['FILE2_DATA']['FILE2_NAME'] : 'Default' }}}</a></li>
                        <li class="pull-right"></li>
                      </ul>
                      <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            <table id="file1Table" class="table table-bordered table-hover">
                              <thead>
                              <tr>
                                <th>TransactionDate</th>
                                <th>TransactionAmount</th>
                                <th>TransactionNarrative</th>
                                <th>TransactionID</th>
                                <th>WalletReference</th>
                                <th>No of suggestions</th>
                                <th>Line no</th>
                                <th></th>
                              </tr>
                              </thead>
                          </table>
                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_2">
                          <table id="file2Table" class="table table-bordered table-hover">
                              <thead>
                              <tr>
                                <th>TransactionDate</th>
                                <th>TransactionAmount</th>
                                <th>TransactionNarrative</th>
                                <th>TransactionID</th>
                                <th>WalletReference</th>
                                <th>No of suggestions</th>
                                <th>Line no</th>
                                <th></th>
                              </tr>
                              </thead>
                          </table>
                        </div>
                        <!-- /.tab-pane -->
                      </div>
                      <!-- /.tab-content -->
                    </div>
                    <!-- nav-tabs-custom -->
              </div><!-- /.box-footer-->
          </div><!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->

      <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content modal-lg">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 style="color:red;"><span class="glyphicon"></span> Suggestions</h4>
        </div>
        <div class="modal-body">
            <table id="suggestionsTable" class="table table-bordered table-hover">
                <thead>
                <tr>
                  <th>TransactionDate</th>
                  <th>TransactionAmount</th>
                  <th>TransactionNarrative</th>
                  <th>TransactionID</th>
                  <th>WalletReference</th>
                  <th>Weight</th>
                </tr>
                </thead>
            </table>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-default btn-default pull-right" 
          data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Ok</button>
        </div>
      </div>
    </div>
  </div> 
@endsection

@section('scripts')
  <script>
    $(function() {

      var showResultsPane = {{ $data['RESULTS'] }};

      //table.dataTable().fnClearTable();
      //table.dataTable().draw()
      
      if (showResultsPane == 0)
         $("#results").hide();
      else{
        //We have results to show
        $("#results").show();
      }
      
      $("#reports").hide();

      $("#unmatched").button().click(function(){
          fillUnmatchedTables(1);
          fillUnmatchedTables(2);
      }); 

      function fillUnmatchedTables(fileIndex){
          var table = $('#file' + fileIndex + 'Table').DataTable( {
          "ajax": "/unmatched?index=" + fileIndex,
          "bDestroy" : true,
          "columnDefs": [ {
              "targets": -1,
              "data": null,
              "defaultContent": "<button>Suggestions</button>"
            } ]
          });

          $("#reports").show();

          $('#file' + fileIndex + 'Table' + ' tbody').on( 'click', 'button', function () {
              var data = table.row( $(this).parents('tr') ).data();
              console.log(data[5]);
              var suggestions = parseInt(data[5]);
              if (suggestions > 0){
                $('#suggestionsTable').DataTable( {
                "ajax": "/suggestions?index=" + fileIndex + "&line=" + data[6],
                "bDestroy" : true
                });

                $("#myModal").modal()
              }
          });
      }

    });
  </script>
@stop