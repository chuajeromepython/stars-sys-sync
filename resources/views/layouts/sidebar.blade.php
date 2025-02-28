@php
	$class = $classification ?? Auth::user()->classification;
@endphp
<aside class="main-sidebar sidebar-light-primary elevation-4">
	<!-- Brand Logo -->
	<a href="/" class="brand-link">
	<img src="/images/logo.png" alt="AdminLTE Logo" class="brand-image img-circle" style="opacity: .8">
	<span class="brand-text font-weight-light">STARS</span>
	</a>

	<!-- Sidebar -->
	<div class="sidebar">
		<div class="user-panel mt-3 pb-3 mb-3 d-flex">
			<div class="image">
			<img src="/images/deped_logo.png" class="img-circle elevation-2 mt-2" alt="User Image">
			</div>
			<div class="info">
			<a href="/account" class="d-block">
				{{ ucwords(strtolower(Auth::user()->person->first_name)) }}
				{{ ucwords(strtolower(Auth::user()->person->last_name)) }}
				
				<p class="text-bold text-primary" style="top: 25px; font-size: 13px; position: absolute;">{{$class}}</p> 
			</a>
			</div>
		</div>
		<nav class="mt-2">
			<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
				<li class="nav-header">MENU</li>
				<li class="nav-item ">
					<a href="/dashboard" class="nav-link 
						{{ ($page['name'] == "Dashboard") ? 'active' : '' }}">
					<i class="nav-icon fa fa-chart-line"></i>
					<p>
						Dashboard
					</p>
					</a>
				</li>
				@if($class != "Student" && $class != "System Administrator")
				<li class="nav-item ">
					<a href="/reports" class="nav-link 
						{{ ($page['name'] == "Report") ? 'active' : '' }}">
						<i class="nav-icon fa fa-file-excel"></i>
						<p>
							Reports
						</p>
					</a>
				</li>
				@endif
				@if($class == "Teacher")
				<li class="nav-item {{ ($page['name'] == "Assessment") ? 'menu-is-opening menu-open' : '' }} ">
					<a href="#" class="nav-link {{ ($page['name'] == "Assessment") ? 'bg-primary' : '' }}">
						<i class="nav-icon fas fa-pen"></i>
						<p>
							Assessment
							<i class="fas fa-angle-left right"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/periodicals" class="nav-link
								@if(array_key_exists("sub_name", $page))
									{{ ($page['sub_name'] == "Periodical") ? 'bg-info' : '' }}
								@endif">
								<i class="far fa-circle nav-icon"></i>
								<p>Periodical Exam</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="/summatives" class="nav-link
								@if(array_key_exists("sub_name", $page))
									{{ ($page['sub_name'] == "Summative") ? 'active' : '' }}
								@endif">
								<i class="far fa-circle nav-icon"></i>
								<p>Summative Exam</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="/ecdcs" class="nav-link
								@if(array_key_exists("sub_name", $page))
									{{ ($page['sub_name'] == "ECDC") ? 'active' : '' }}
								@endif">
								<i class="far fa-circle nav-icon"></i>
								<p>ECDC</p>
							</a>
						</li>
					</ul>
				</li>
				@endif
			
				@if($class == "School Head" || $class == "Teacher")
				<li class="nav-item ">
					<a href="/classrooms" class="nav-link 
						{{ ($page['name'] == "Classroom") ? 'active' : '' }}">
					<i class="nav-icon fa fa-home"></i>
					<p>
						Classrooms
					</p>
					</a>
				</li>	
				@endif
				<li class="nav-header">REFERENCE LIBRARY</li>
				@if($class == "Teacher")
				<li class="nav-item ">
					<a href="/item_banks" class="nav-link 
						{{ ($page['name'] == "Item Bank") ? 'active' : '' }}">
						<i class="nav-icon fa fa-building"></i>
						<p>
							Item Bank
						</p>
					</a>
				</li>
				@endif
				@if($class == "System Administrator")
				<li class="nav-item ">
					<a href="/divisions" class="nav-link 
						{{ ($page['name'] == "Division") ? 'active' : '' }}">
					<i class="nav-icon fa fa-map"></i>
					<p>
						Divisions
					</p>
					</a>
				</li>
				@endif
				@if($class == "Student")
				<li class="nav-item ">
					<a href="/students/class_assessments" class="nav-link 
						{{ ($page['name'] == "Class Assessment") ? 'active' : '' }}">
					<i class="nav-icon fa fa-pen"></i>
					<p>
							Class Assessment
					</p>
					</a>
				</li>	
				@endif
				@if($class == "Division Administrator" || $class == "System Administrator")
					<li class="nav-item ">
						<a href="/users" class="nav-link 
							{{ ($page['name'] == "User") ? 'active' : '' }}">
						<i class="nav-icon fa fa-user"></i>
						<p>
							Users
						</p>
						</a>
					</li>
				@endif
				
				@if($class == "System Administrator")
				<li class="nav-item ">
					<a href="/trails" class="nav-link 
						{{ ($page['name'] == "Trails") ? 'active' : '' }}">
					<i class="nav-icon fa fa-hourglass"></i>
					<p>
						Trails
					</p>
					</a>
				</li>
				@endif
				@if($class == "Division Administrator")
					<li class="nav-item ">
						<a href="/academic_years" class="nav-link 
							{{ ($page['name'] == "Academic Year") ? 'active' : '' }}">
						<i class="nav-icon fa fa-calendar"></i>
						<p>
							Academic Years
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/districts" class="nav-link 
							{{ ($page['name'] == "District") ? 'active' : '' }}">
						<i class="nav-icon fa fa-map-pin"></i>
						<p>
							Districts
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/schools" class="nav-link 
							{{ ($page['name'] == "School") ? 'active' : '' }}">
						<i class="nav-icon fa fa-building"></i>
						<p>
							Schools
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/grade_levels" class="nav-link 
							{{ ($page['name'] == "Grade Level") ? 'active' : '' }}">
						<i class="nav-icon fa fa-star"></i>
						<p>
							Grade Levels
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/subjects" class="nav-link 
							{{ ($page['name'] == "Subject") ? 'active' : '' }}">
						<i class="nav-icon fa fa-book"></i>
						<p>
							Subjects
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/semesters" class="nav-link 
							{{ ($page['name'] == "Semester") ? 'active' : '' }}">
						<i class="nav-icon fa fa-clipboard"></i>
						<p>
							Semesters
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/tracks" class="nav-link 
							{{ ($page['name'] == "Track") ? 'active' : '' }}">
						<i class="nav-icon fa fa-road"></i>
						<p>
							Tracks
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/strands" class="nav-link 
							{{ ($page['name'] == "Strand") ? 'active' : '' }}">
						<i class="nav-icon fa fa-chart-bar"></i>
						<p>
							Strands
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/courses" class="nav-link 
							{{ ($page['name'] == "Course") ? 'active' : '' }}">
						<i class="nav-icon fa fa-atom"></i>
						<p>
							Courses
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/competencies" class="nav-link 
							{{ ($page['name'] == "Competency") ? 'active' : '' }}">
						<i class="nav-icon fa fa-check"></i>
						<p>
							Competencies
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/ecdc_domains" class="nav-link 
							{{ ($page['name'] == "ECDC Domains") ? 'active' : '' }}">
						<i class="nav-icon fa fa-child"></i>
						<p>
							ECDC Domains
						</p>
						</a>
					</li>
				@endif
				@if($class == "School Head")
					<li class="nav-item ">
						<a href="/department_heads" class="nav-link 
							{{ ($page['name'] == "Department Head") ? 'active' : '' }}">
						<i class="nav-icon fa fa-users"></i>
						<p>
							Department Heads
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/teachers" class="nav-link 
							{{ ($page['name'] == "Teacher") ? 'active' : '' }}">
						<i class="nav-icon fa fa-chalkboard-teacher"></i>
						<p>
							Teachers
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/students" class="nav-link 
							{{ ($page['name'] == "Student") ? 'active' : '' }}">
						<i class="nav-icon fa fa-user-graduate"></i>
						<p>
							Students
						</p>
						</a>
					</li>
					<li class="nav-item ">
						<a href="/sections" class="nav-link 
							{{ ($page['name'] == "Section") ? 'active' : '' }}">
							<i class="nav-icon fa fa-bookmark"></i>
							<p>
								Sections
							</p>
						</a>
					</li>
				@endif

			</ul>
		</nav>
		<!-- /.sidebar-menu -->
	</div>
	<!-- /.sidebar -->
</aside>