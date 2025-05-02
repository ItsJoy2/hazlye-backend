		<!-- Sidebar -->
		<div class="sidebar" data-background-color="dark">
			<div class="sidebar-logo">
				<!-- Logo Header -->
				<div class="logo-header" data-background-color="dark">

					<a href="{{route('admin.dashboard')}}" class="logo">
                        @if($generalSettings->logo)
                        <img src="{{ Storage::url($generalSettings->logo) }}" alt="{{ $generalSettings->app_name }}" class="navbar-brand" height="20">
                    @else
                        <h1>{{ $generalSettings->app_name ?? 'App Name' }}</h1>
                    @endif
					</a>
					<div class="nav-toggle">
						<button class="btn btn-toggle toggle-sidebar">
							<i class="gg-menu-right"></i>
						</button>
						<button class="btn btn-toggle sidenav-toggler">
							<i class="gg-menu-left"></i>
						</button>
					</div>
					<button class="topbar-toggler more">
						<i class="gg-more-vertical-alt"></i>
					</button>

				</div>
				<!-- End Logo Header -->
			</div>
			<div class="sidebar-wrapper scrollbar scrollbar-inner">
				<div class="sidebar-content">
					<ul class="nav nav-secondary">
						<li class="nav-item active">
							<a href="{{route('admin.dashboard')}}">
								<i class="fas fa-home"></i>
								<p>Dashboard</p>
							</a>
						</li>
                        <li class="nav-item">
							<a  href="">
								<i class="fas fa-users"></i>
								<p>Users</p>
								{{-- <span class="caret"></span> --}}
							</a>
							{{-- <div class="collapse" id="users">
								<ul class="nav nav-collapse">
									<li>
										<a href="{{route('admin.users.index')}}">
											<span class="sub-item">All Users</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.users.active')}}">
											<span class="sub-item">Active Users</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.users.inactive')}}">
											<span class="sub-item">Inactive Users</span>
										</a>
									</li>
                                    <li>
										<a href="{{route('admin.users.block')}}">
											<span class="sub-item">Block Users</span>
										</a>
									</li>
                                    <li>
										<a href="{{route('admin.wallet.block')}}">
											<span class="sub-item">Wallet Block Users</span>
										</a>
									</li>

								</ul>
							</div> --}}
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#categories">
								<i class="far fa-money-bill-alt"></i>
								<p>Categories</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="categories">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.categories.index')}}">
											<span class="sub-item">All Categories</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.categories.create')}}">
											<span class="sub-item">Create Category</span>
										</a>
									</li>
								</ul>
							</div>
						</li>

                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#colors">
								<i class="far fa-money-bill-alt"></i>
								<p>Colors</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="colors">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.colors.index')}}">
											<span class="sub-item">All Colors</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.colors.create')}}">
											<span class="sub-item">Color Management</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#sizes">
								<i class="far fa-money-bill-alt"></i>
								<p>Sizes</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="sizes">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.sizes.index')}}">
											<span class="sub-item">All Sizes</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.sizes.create')}}">
											<span class="sub-item">Add Size</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#products">
								<i class="far fa-money-bill-alt"></i>
								<p>Products</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="products">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.products.index')}}">
											<span class="sub-item">All Products</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.products.create')}}">
											<span class="sub-item">Add Products</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#settings">
								<i class="fas fa-cog"></i>
								<p>Settings</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="settings">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.general.settings')}}">
											<span class="sub-item">General Settings</span>
										</a>
									</li>

								</ul>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->