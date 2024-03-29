@extends('layout.dispatch')

@section('content')
<div style="margin-left:150px;" class="col-md-10">

    @if (Session::has('success'))

        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
            <span class="fa fa-check-square-o"></span><b> Success : {!! Session::get('success') !!}</b>
        </div>

    @elseif(Session::has('error'))

        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
            <span class="fa fa-exclamation"></span><b> Error : {!! Session::get('error') !!}</b>
        </div>

    @endif

    <!-- BEGIN SAMPLE FORM PORTLET-->
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-red-sunglo">
                <i class="fa fa-truck font-red-sunglo"></i>
                <span class="caption-subject uppercase"> {{ $dispatch->tripTicket }}
                    Details</span><br>&nbsp;&nbsp;&nbsp;
                <span style="font-size: 17px;color:black;">Status: {{ $dispatch->Status }} </span>
            </div>

            <div style="float:right;">
                <a class="btn yellow" href="{{ route('vehicle.request.dispatch_printout', ['id' => $id]) }}" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
                <a style="display:{{ $cancelled }}" class="btn blue" href="{{ route('vehicle.request.edit.dispatch_details', ['id' => $id]) }}">
                    <i class="fa fa-edit"></i> Edit/Update 
                </a>
                <a style="display:{{ $cancelled }}" data-toggle='modal' class='btn red' href='#cancel-{{ $id }}'>
                    <span class='glyphicon glyphicon-remove-circle'></span> Cancel
                </a>

                <div class="modal fade" id="cancel-{{ $id }}" tabindex="-1" role="basic" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="post" action="{{ route('vehicle.request.cancel.dispatch_details') }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <input type="hidden" name="tid" value="{{ $id }}">
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-hidden="true"></button>
                                    <h4 class="modal-title"><b>Confirmation</b></h4>
                                </div>
                                <div class="modal-body"> Are you sure you want to <b>Cancel</b> this Trip Ticket? </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-circle dark btn-outline"
                                        data-dismiss="modal"><i class="fa fa-backward"></i> Back</button>
                                    <button type="submit" name="cancel_tid" class="btn btn-circle blue"><span
                                            class="glyphicon glyphicon-remove-circle"></span> Confirm Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="portlet-body">
            <div class="tab-content">
                <!-- PERSONAL INFO TAB -->
                <div class="tab-pane active">
                    <div class="row">
                        <div class="form-group col-md-12">

                            <div class="col-md-3">
                                <label class="control-label">Date Out</label>
                                <div class="input-group date form_datetime col-md-12" data-date=""
                                    data-date-format="yyyy-mm-dd HH:ii p" data-link-field="date_out">
                                    <div class="input-icon">
                                        <i class="fa fa-calendar font-yellow"></i>
                                        <input class="form-control" size="16" type="text"
                                            value="{{ Carbon\Carbon::parse($dispatch->dateStart)->format('Y-m-d h:i:s') }}"
                                            readonly>
                                    </div>
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                                    <input type="hidden" name="date_out" id="date_out"
                                        value="{{ Carbon\Carbon::parse($dispatch->dateStart)->format('Y-m-d h:i:s') }}" />
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="control-label">Department</label>
                                <div class="input-icon">
                                    <i class="fa fa-building-o font-yellow"></i>
                                    <input readonly type="text" class="form-control" value="{{ $dispatch->deptId }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="control-label">Vehicle</label>
                                <input readonly class="form-control" type="text" value="{{ $dispatch->type }}">
                            </div>

                            <div class="col-md-3">
                                <label class="control-label">Trip & Ticket No.</label>
                                <input type="text" class="form-control" value="{{ $dispatch->tripTicket }}" readonly>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="col-md-3">
                                <label class="control-label">Application Date</label>
                                <div class="input-group date form_datetime col-md-12" data-date=""
                                    data-date-format="yyyy-mm-dd HH:ii p" data-link-field="dt_from">
                                    <div class="input-icon">
                                        <i class="fa fa-calendar font-yellow"></i>
                                        <input class="form-control" size="16" type="text"
                                            value="{{ Carbon\Carbon::parse($dispatch->addedDate)->format('Y-m-d h:i:s') }}"
                                            readonly>
                                    </div>
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                                    <input type="hidden" name="app_date" id="dt_from"
                                        value="{{ Carbon\Carbon::parse($dispatch->addedDate)->format('Y-m-d h:i:s') }}" />
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="control-label">Driver</label>
                                <input readonly type="text" class="form-control"
                                    @if($drivers)
                                    value="{{ strtoupper($drivers->driver_name) }}">
                                    @else
                                   
                                    @endif

                            </div>

                            {{-- <div class="col-md-3">
                                <label class="control-label">From</label>
                                <div class="input-icon">
                                    <i class="fa fa-globe font-yellow"></i>
                                    <input readonly type="text" class="form-control" value="{{ strtoupper($origin) }}">
                                </div>
                            </div> --}}

                            <div class="col-md-3">
                                <label class="control-label">To</label>
                                <div class="input-icon">
                                    <i class="fa fa-globe font-yellow"></i>
                                    <input readonly type="text" class="form-control"
                                        value="{{ strtoupper($destination) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="col-md-12">
                                <label class="control-label">Purpose</label>
                                <div class="input-icon">
                                    <i class="fa fa-comment-o font-yellow"></i>
                                    <textarea readonly
                                        class="form-control">{{ strtoupper($dispatch->purpose) }}</textarea>
                                </div>

                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="col-md-12">
                                <div class="form-group multiple-form-group">
                                    <label>Passengers</label>
                                    <ul class="list-inline">
                                        @foreach ($passengers as $item)
                                            <li><input readonly class="form-control" type="text" value="{{ $item }}" />
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="caption font-red-sunglo">
                                <i class="fa fa-automobile font-red-sunglo"></i>
                                <span class="caption-subject bold uppercase" style="font-size: 16px;"> Return Slip
                                    Form</span>
                            </div>
                            <hr>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="col-md-3">
                                <label class="control-label">Date Return</label>
                                <div class="input-group date form_datetime col-md-12" data-date=""
                                    data-date-format="yyyy-mm-dd HH:ii p" data-link-field="date_return">
                                    <div class="input-icon">
                                        <i class="fa fa-calendar font-yellow"></i>
                                        <input class="form-control" size="16" type="text"
                                            value="{{ $dispatch->odometer_end }}" readonly>
                                    </div>
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                                    <input type="hidden" name="date_return" id="date_return" value="" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">Odometer End</label>
                                <div class="input-icon">
                                    <i class="fa fa-tachometer font-yellow"></i>
                                    <input readonly type="number" class="form-control"
                                        value="{{ $dispatch->odometer_end }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">Fuel Type</label>
                                <input readonly class="form-control" type="text"
                                    value="{{ strtoupper($dispatch->fuel_added_type) }}">
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="col-md-3">
                                <label class="control-label">Fuel Requested Qty</label>
                                <div class="input-icon">
                                    <i class="fa fa-fire font-yellow"></i>
                                    <input readonly type="number" class="form-control"
                                        value="{{ $dispatch->fuel_requested_qty }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="control-label">Actual Qty</label>
                                <input readonly class="form-control" type="text"
                                    value="{{ $dispatch->fuel_added_qty }}">
                            </div>

                            <div class="col-md-3">
                                <label class="control-label">UOM</label>
                                <div class="input-icon">
                                    <i class="icon-calculator font-yellow"></i>
                                    <input readonly type="text" class="form-control" value="{{ $dispatch->oum }}">
                                </div>
                            </div>
                            <div style="margin-top: 180px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END SAMPLE FORM PORTLET-->
    </div>
    <div class="clearfix"></div>
</div>
@endsection

@push('metronic-scripts')
   <script src="{{ asset('metronic/assets/global/plugins/jquery-1.11.0.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/jquery-migrate-1.2.1.min.js') }}" type="text/javascript"></script>
   <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
   <script src="{{ asset('metronic/assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/jquery.blockui.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/jquery.cokie.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/uniform/jquery.uniform.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}" type="text/javascript"></script>
   <!-- END CORE PLUGINS -->
   <!-- BEGIN PAGE LEVEL PLUGINS -->
   <script type="text/javascript" src="{{ asset('metronic/assets/global/plugins/bootstrap-select/bootstrap-select.min.js') }}"></script>
   <script type="text/javascript" src="{{ asset('metronic/assets/global/plugins/select2/select2.min.js') }}"></script>
   <script type="text/javascript" src="{{ asset('metronic/assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js') }}"></script>
   <script src="{{ asset('metronic/assets/global/plugins/bootstrap-toastr/toastr.min.js') }}"></script>
   <script src="{{ asset('metronic/assets/global/plugins/jquery.pulsate.min.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/global/scripts/metronic.js') }}" type="text/javascript"></script>
   <script src="{{ asset('metronic/assets/admin/layout/scripts/layout.js') }}" type="text/javascript"></script>
   <script type="text/javascript" src="{{ asset('metronic/datepicker/js/jquery-1.8.3.min.js') }}" charset="UTF-8"></script>
   <script type="text/javascript" src="{{ asset('metronic/datepicker/js/bootstrap-datetimepicker.js') }}" charset="UTF-8"></script>
   <script src="{{ asset('notifications.js') }}"></script>
   <script src="{{ asset('comments.js') }}"></script>
@endpush
