		<!-- Sidebar -->
		<div class="sidebar" data-background-color="dark">
			<div class="sidebar-logo">
				<!-- Logo Header -->
				<div class="logo-header" data-background-color="dark">

					<a href="{{route('admin.dashboard')}}" class="logo">
                        @if($generalSettings->logo)
                        <img src="{{ asset('storage/' . str_replace('public/', '', $generalSettings->logo)) }}" alt="{{ $generalSettings->app_name }}" class="navbar-brand" height="50">
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
							<a  data-bs-toggle="collapse" href="#orders">
								<i class="fas fa-users"></i>
								<p>Orders</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="orders">
								<ul class="nav nav-collapse">
									<li>
										<a href="{{route('admin.orders.index')}}">
											<span class="sub-item">All Orders</span>
										</a>
									</li>
									<li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="nav-link">
                                            <span class="sub-item">Pending </span>
                                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'pending')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'hold']) }}" class="nav-link">
                                            <span class="sub-item">Hold</span>
                                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'hold')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="nav-link">
                                            <span class="sub-item">Order Confirmed</span>
                                            <span class="badge badge-info float-right">
                                                {{ App\Models\Order::where('status', 'processing')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="nav-link">
                                            <span class="sub-item">Ready To Shipped</span>
                                            <span class="badge badge-primary float-right">
                                                {{ App\Models\Order::where('status', 'shipped')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'courier_delivered']) }}" class="nav-link">
                                            <span class="sub-item">Courier Delivered</span>
                                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'courier_delivered')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="nav-link">
                                            <span class="sub-item">Delivered Orders</span>
                                            <span class="badge badge-success float-right">
                                                {{ App\Models\Order::where('status', 'delivered')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a href="{{ route('admin.orders.incomplete') }}" class="nav-link">
                                            <span class="sub-item">Incomplete Orders</span>
                                            <span class="badge badge-secondary float-right">
                                                {{ App\Models\Order::where('status', 'incomplete')->count() }}
                                            </span>
                                        </a>
                                    </li> --}}
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="nav-link">
                                            <span class="sub-item">Cancelled Orders</span>
                                            <span class="badge badge-danger float-right">
                                                {{ App\Models\Order::where('status', 'cancelled')->count() }}
                                            </span>
                                        </a>
                                    </li>

								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a href={{route('admin.orders.incomplete')}}>
								<i class="far fa-money-bill-alt"></i>
                                <span class="sub-item">Incomplete Orders</span>
                                <span class="badge badge-secondary float-right">
                                    {{ App\Models\Order::where('status', 'incomplete')->count() }}
                                </span>
							</a>
						</li>
                        <li class="nav-item">
							<a href="{{route('admin.orders.shipped')}}">
								<i class="far fa-money-bill-alt"></i>
								<p>Courier Orders</p>
								{{-- <span class="caret"></span> --}}
							</a>
							{{-- <div class="collapse" id="categories">
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
							</div> --}}
						</li>
                        <li class="nav-item">
							<a href={{route('admin.customers.index')}}>
								<i class="far fa-money-bill-alt"></i>
								<p>Customers</p>
								{{-- <span class="caret"></span> --}}
							</a>
							{{-- <div class="collapse" id="customers">
								<ul class="nav nav-collapse">
                                    <li>
										<a  href="{{route('admin.customers.index')}}">
											<span class="sub-item">Customers List</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.customers.blocked')}}">
											<span class="sub-item">Block Customers</span>
										</a>
									</li>
								</ul>
							</div> --}}
						</li>
                        <li class="nav-item">
							<a href={{route('admin.customers.blocked')}}>
								<i class="far fa-money-bill-alt"></i>
								<p>Block Customers</p>
								{{-- <span class="caret"></span> --}}
							</a>
							{{-- <div class="collapse" id="customers">
								<ul class="nav nav-collapse">
                                    <li>
										<a  href="{{route('admin.customers.index')}}">
											<span class="sub-item">Customers List</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.customers.blocked')}}">
											<span class="sub-item">Block Customers</span>
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
							<a data-bs-toggle="collapse" href="#coupons">
								<i class="far fa-money-bill-alt"></i>
								<p>Coupons</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="coupons">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.coupons.index')}}">
											<span class="sub-item">All Coupons</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.coupons.create')}}">
											<span class="sub-item">Add Coupon</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#delivery-options">
								<i class="far fa-money-bill-alt"></i>
								<p>Delivery Options</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="delivery-options">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.delivery-options.index')}}">
											<span class="sub-item">All Options</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.delivery-options.create')}}">
											<span class="sub-item">Add Delivery Options</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a href="{{route('admin.homepage-sections.index')}}">
								<i class="far fa-money-bill-alt"></i>
								<p>Home Section</p>
								{{-- <span class="caret"></span> --}}
							</a>
							{{-- <div class="collapse" id="categories">
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
							</div> --}}
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#banners">
								<i class="far fa-money-bill-alt"></i>
								<p>Banners</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="banners">
								<ul class="nav nav-collapse">
                                    <li>
										<a  href="{{route('admin.banners.index')}}">
											<span class="sub-item">Banners All</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.banners.create')}}">
											<span class="sub-item">Create Banners</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#couriers">
								<i class="far fa-money-bill-alt"></i>
								<p>Couriers Management</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="couriers">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.couriers.index')}}">
											<span class="sub-item">Couriers List</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.couriers.create')}}">
											<span class="sub-item">Add Courier</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#reviews">
								<i class="fas fa-cog"></i>
								<p>Reviews</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="reviews">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.reviews.index')}}">
											<span class="sub-item">All Reviews</span>
										</a>
									</li>
                                    <li>
										<a href="{{route('admin.reviews.create')}}">
											<span class="sub-item">Create Review</span>
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