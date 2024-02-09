@if ($message = Session::get('success'))
<div data-growl="container" class="alert alert-success alert-dismissable growl-animated animated fadeInDown fadeOutUp background-success" role="alert" data-growl-position="top-right" style="position: fixed; margin: 0px; z-index: 999999; display: inline-block; top: 200px; right: 30px;">
    <span data-growl="icon"></span>
    <button type="button" class="close" data-dismiss="alert" style="display: inline-block; margin-left: 10px; z-index: 999998;">
        <span aria-hidden="true">×</span>
        <span class="sr-only">Close</span>
    </button>
    <span data-growl="icon" class="fa fa-check"></span>
    <span data-growl="title">{{ $message }}</span>
    <a href="#" data-growl="url"></a>
</div>
@endif

@if ($message = Session::get('error'))
<div data-growl="container" class="alert alert-danger alert-dismissable growl-animated animated fadeInDown fadeOutUp background-danger" role="alert" data-growl-position="top-right" style="position: fixed; margin: 0px; z-index: 999999; display: inline-block; top: 200px; right: 30px;">
    <span data-growl="icon"></span>
    <button type="button" class="close" data-dismiss="alert" style="display: inline-block; margin-left: 10px; z-index: 999998;">
        <span aria-hidden="true">×</span>
        <span class="sr-only">Close</span>
    </button>
    <span data-growl="icon" class="ti-close"></span>
    <span data-growl="title">{{ $message }}</span>
    <a href="#" data-growl="url"></a>
</div>
@endif

@if ($message = Session::get('warning'))
<div data-growl="container" class="alert alert-warning alert-dismissable growl-animated animated fadeInDown fadeOutUp background-warning" role="alert" data-growl-position="top-right" style="position: fixed; margin: 0px; z-index: 999999; display: inline-block; top: 200px; right: 30px;">
    <span data-growl="icon"></span>
    <button type="button" class="close" data-dismiss="alert" style="display: inline-block; margin-left: 10px; z-index: 999998;">
        <span aria-hidden="true">×</span>
        <span class="sr-only">Close</span>
    </button>
    <span data-growl="icon" class="ti-alert"></span>
    <span data-growl="title">{{ $message }}</span>
    <a href="#" data-growl="url"></a>
</div>
@endif

@if ($message = Session::get('info'))
<div data-growl="container" class="alert alert-info alert-dismissable growl-animated animated fadeInDown fadeOutUp background-info" role="alert" data-growl-position="top-right" style="position: fixed; margin: 0px; z-index: 999999; display: inline-block; top: 200px; right: 30px;">
    <span data-growl="icon"></span>
    <button type="button" class="close" data-dismiss="alert" style="display: inline-block; margin-left: 10px; z-index: 999998;">
        <span aria-hidden="true">×</span>
        <span class="sr-only">Close</span>
    </button>
    <span data-growl="icon" class="ti-info-alt"></span>
    <span data-growl="title">{{ $message }}</span>
    <a href="#" data-growl="url"></a>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger background-danger">
    <div data-growl="container" class="alert alert-danger alert-dismissable growl-animated animated fadeInDown fadeOutUp background-danger" role="alert" data-growl-position="top-right" style="position: fixed; margin: 0px; z-index: 999999; display: inline-block; top: 200px; right: 30px;">
        <span data-growl="icon"></span>
        <button type="button" class="close" data-dismiss="alert" style="display: inline-block; margin-left: 10px; z-index: 999998;">
            <span aria-hidden="true">×</span>
            <span class="sr-only">Close</span>
        </button>
        <span data-growl="icon" class="ti-close"></span>
        <span data-growl="title"> Verifique os campos e tente novamente.</span>
        <a href="#" data-growl="url"></a>
    </div>
</div>
@endif
