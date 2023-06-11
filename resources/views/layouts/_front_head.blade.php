<header id="header" class="fixed-top header-transparent">
	<div
		class="container d-flex align-items-center ">

		<div class="logo">
			<!-- <h1 class="text-light"><a href="index.html"><span>Squadfree</span></a></h1>-->
			<!-- Uncomment below if you prefer to use an image logo -->
			<a href="index.html"><img src="{{ URL::asset('img/logo-white.png') }}" alt=""
				class="img-fluid"></a>
		</div>

		<nav id="navbar" class="navbar">
			<ul>
				<li><a class="nav-link scrollto active" href="{{ url("/") }}">Home</a></li>
				<li><a class="nav-link scrollto" href="{{ route('services') }}">Services</a></li>
				<li><a class="nav-link scrollto" href="{{ route('price') }}">Price</a></li>
				<li><a class="nav-link scrollto" href="{{ route('about-us') }}">About Us</a></li>
				<li><a class="nav-link scrollto" href="{{ route('contect-us') }}">Contact</a></li>
				@if(\Auth::user())
				<li><a class="nav-link scrollto" href="{{ URL('logout') }}">Logout</a></li>
				@else
				<li><a class="nav-link scrollto" href="{{ URL('login') }}">Login</a></li>
				@endif
			</ul>
			<i class="bi bi-list mobile-nav-toggle"></i>
		</nav>
		<!-- .navbar -->

	</div>
</header>