<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navigation">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item dropdown">
                <a class="nav-link <?php if (isset($page) && $page == "flights") echo "active"; ?> dropdown-toggle" role="button" data-toggle="dropdown">
                    Flights
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="flight.php">Add Flight</a>
                    <a class="dropdown-item" href="flight-search.php">Search Flight</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="flight-schedule.php">Add Flight Schedule</a>
                    <a class="dropdown-item" href="flight-schedule-search.php">Search Flight Schedule</a>
                </div>
            </li>
            <li class="nav-item <?php if (isset($page) && $page == "ticketSales") echo "active"; ?> dropdown">
                <a class="nav-link dropdown-toggle" role="button" data-toggle="dropdown">
                    Ticket Sales
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="flight-ticket.php">Add Ticket</a>
                    <a class="dropdown-item" href="flight-ticket-search.php">Search Ticket</a>
                    <a class="dropdown-item" href="ticket-class.php">Update Ticket Classes</a>
                </div>
            </li>
            <li class="nav-item <?php if (isset($page) && $page == "flightCrew") echo "active"; ?>">
                <a class="nav-link" href="schedule_search-1.php">Flight Crew</a>
            </li>
            <li class="nav-item <?php if (isset($page) && $page == "boardingStatus") echo "active"; ?>">
                <a class="nav-link" href="MonitorBoardingSearch.php">Boarding Status</a>
            </li>
            <li class="nav-item <?php if (isset($page) && $page == "reports") echo "active"; ?>">
                <a class="nav-link" href="MonitorFlightReportSearch.php">Reports</a>
            </li>
        </ul>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
            <li class="nav-item">
                <i class="fas fa-3x fa-user-circle"></i>
            </li>
        </ul>
    </div>
</nav>