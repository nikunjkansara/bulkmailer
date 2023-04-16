<!DOCTYPE html>
<html lang="en">
<head>
<title>@yield('title') - {{ \horsefly\Model\Setting::get("site_name") }}</title>
@include('layouts._favicon') 

@include('layouts._front_css')

@include('layouts._front_head')
@include('layouts._front_subpage')
@include('layouts._front_breadcrumbs')

</head>

<body>
	<!-- Page container -->
	<div class="page-container" style="min-height: 100vh">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">

				<!-- main inner content -->
				@yield('content')

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->

	</div>
	<!-- /page container -->

	{!! \horsefly\Model\Setting::get('custom_script') !!}
	
	@include('layouts._front_footer')
	@include('layouts._front_js')
</body>
</html>
