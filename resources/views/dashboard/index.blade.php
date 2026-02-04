@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row clearfix row-deck">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card number-chart">
                <div class="body">
                    <span class="text-uppercase">New Sessions</span>
                    <h4 class="mb-0 mt-2">22,500 <i class="fa fa-level-up font-12"></i></h4>
                    <small class="text-muted">Analytics for last week</small>
                </div>
                <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%"
                    data-height="50px" data-line-Width="1" data-line-Color="#39afa6" data-fill-Color="#73cec7">4,1,5,2,7,3,4
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card number-chart">
                <div class="body">
                    <span class="text-uppercase">Goal Completions</span>
                    <h4 class="mb-0 mt-2">1,12,500</h4>
                    <small class="text-muted">Analytics for last week</small>
                </div>
                <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%"
                    data-height="50px" data-line-Width="1" data-line-Color="#ffa901" data-fill-Color="#efc26b">1,4,2,3,6,2
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card number-chart">
                <div class="body">
                    <span class="text-uppercase">TIME ON SITE</span>
                    <h4 class="mb-0 mt-2">1,070</h4>
                    <small class="text-muted">Analytics for last week</small>
                </div>
                <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%"
                    data-height="50px" data-line-Width="1" data-line-Color="#38c172" data-fill-Color="#84d4a6">1,4,2,3,1,5
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card number-chart">
                <div class="body">
                    <span class="text-uppercase">BOUNCE RATE</span>
                    <h4 class="mb-0 mt-2">10K</h4>
                    <small class="text-muted">Analytics for last week</small>
                </div>
                <div class="sparkline" data-type="line" data-spot-Radius="0" data-offset="90" data-width="100%"
                    data-height="50px" data-line-Width="1" data-line-Color="#226fd8" data-fill-Color="#7ea7de">1,3,5,1,4,2
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card top_widget">
                <div class="body">
                    <div class="icon"><i class="fa fa-flag"></i> </div>
                    <div class="content">
                        <div class="text mb-2 text-uppercase">Sessions</div>
                        <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i>
                                13%</span></h4>
                        <small class="text-muted">Analytics for last week</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card top_widget">
                <div class="body">
                    <div class="icon"><i class="fa fa-users"></i> </div>
                    <div class="content">
                        <div class="text mb-2 text-uppercase">Users</div>
                        <h4 class="number mb-0">25K <span class="font-12 text-muted"><i class="fa fa-level-down"></i>
                                7%</span></h4>
                        <small class="text-muted">Analytics for last week</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card top_widget">
                <div class="body">
                    <div class="icon"><i class="fa fa-user"></i> </div>
                    <div class="content">
                        <div class="text mb-2 text-uppercase">VISITORS</div>
                        <h4 class="number mb-0">21K <span class="font-12 text-muted"><i class="fa fa-level-down"></i>
                                4%</span></h4>
                        <small class="text-muted">Analytics for last week</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card top_widget">
                <div class="body">
                    <div class="icon"><i class="fa fa-thumbs-up"></i> </div>
                    <div class="content">
                        <div class="text mb-2 text-uppercase">LIKES</div>
                        <h4 class="number mb-0">53K <span class="font-12 text-muted"><i class="fa fa-level-up"></i>
                                15%</span></h4>
                        <small class="text-muted">Analytics for last week</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Message Context (Toastr Demo)</h2>
                </div>
                <div class="body">
                    <button type="button" class="btn btn-primary btn-toastr" data-context="info"
                        data-message="This is general theme info" data-position="bottom-right">General Info</button>
                    <button type="button" class="btn btn-success btn-toastr" data-context="success"
                        data-message="This is success info" data-position="bottom-right">Success Info</button>
                    <button type="button" class="btn btn-warning btn-toastr" data-context="warning"
                        data-message="This is warning info" data-position="bottom-right">Warning Info</button>
                    <button type="button" class="btn btn-danger btn-toastr" data-context="error"
                        data-message="This is error info" data-position="bottom-right">Error Info</button>
                </div>
            </div>
        </div>
    </div>
@endsection