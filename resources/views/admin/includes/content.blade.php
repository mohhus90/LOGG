<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">@yield('start')</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item "><a href="#">@yield('home')&nbsp</a></li>
              <li class="breadcrumb-item active">@yield('startpage')&nbsp</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        @if (Session::has('error'))
          <div class= 'alert alert-danger error text-center' role="alert">
            {{Session::get('error')}}
          </div> 
        @endif
        @if (Session::has('erorr'))
          <div class= 'alert alert-danger erorr text-center' role="alert">
            {{Session::get('erorr')}}
          </div> 
        @endif
        @if (Session::has('success'))
          <div class= 'alert alert-success success text-center' role="alert">
            {{Session::get('success')}}
          </div> 
        @endif
        @if (Session::has('errorUpdate'))
          <div class= 'alert alert-danger erorr text-center' role="alert">
            {{Session::get('errorUpdate')}}
          </div> 
        @endif
        
        @yield('content')
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>