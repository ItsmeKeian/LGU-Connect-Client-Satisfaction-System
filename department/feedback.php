<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Feedback Inbox | LGU-Connect</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/sidebar_header.css"/>
<link rel="stylesheet" href="../assets/css/feedback_inbox.css"/>
</head>
<body>
<div class="app-shell">

  <!-- ══════════════ SIDEBAR ══════════════ -->
  <aside class="sidebar" id="sidebar">

    <div class="sb-brand">
      <img src="../assets/img/san_julian_logo.png" class="sb-logo-img" alt="Logo"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"/>
      <div class="sb-logo-fallback" style="display:none">SJ</div>
      <div class="sb-brand-text">
        <div class="sb-name">LGU<span>-Connect</span></div>
        <div class="sb-sub">San Julian, E. Samar</div>
      </div>
    </div>

    <div class="sb-role">
      <div class="role-dot"></div>
      <div>
        <div class="role-name" id="sidebarUserName">Department Admin</div>
        <div class="role-sub">Department Administrator</div>
      </div>
    </div>

    <div class="sb-section">My Department</div>
    <ul class="sb-nav">
      <li><a href="dept_dashboard.php">
        <span class="nav-icon">&#9962;</span> My Dashboard
      </a></li>
      <li><a href="feedback_inbox.php" class="active">
        <span class="nav-icon">&#128203;</span> Feedback Inbox
        <span class="nav-badge" id="sbFeedbackCount">28</span>
      </a></li>
      <li><a href="qrcode.php">
        <span class="nav-icon">&#9636;</span> My QR Code
      </a></li>
    </ul>

    <div class="sb-section">Reports</div>
    <ul class="sb-nav">
      <li><a href="my_csmr.php">
        <span class="nav-icon">&#128196;</span> Generate CSMR
      </a></li>
      <li><a href="analytics.php">
        <span class="nav-icon">&#128200;</span> My Analytics
      </a></li>
      <li><a href="export.php">
        <span class="nav-icon">&#128228;</span> Export Data
      </a></li>
    </ul>

    <div class="sb-section">Account</div>
    <ul class="sb-nav">
      <li><a href="profile.php">
        <span class="nav-icon">&#128100;</span> My Profile
      </a></li>
      <li><a href="settings.php">
        <span class="nav-icon">&#9881;</span> Settings
      </a></li>
    </ul>

    <div class="sb-footer">
      <a href="../logout.php">
        <span class="nav-icon">&#10548;</span> Sign Out
      </a>
    </div>

  </aside>
  <!-- /SIDEBAR -->

  <!-- ══════════════ MAIN AREA ══════════════ -->
  <div class="main-area">

    <!-- Topbar -->
    <div class="topbar">
      <button class="menu-toggle" id="menuToggle">&#9776;</button>
      <div class="topbar-title" id="topbarTitle">
        <h1>Feedback Inbox</h1>
        <span class="tb-subtitle">View and manage citizen feedback</span>
      </div>
      <div class="topbar-actions">
        <div class="search-wrap">
          <span class="search-icon">&#128269;</span>
          <input type="text" class="tb-search" id="fbSearch" placeholder="Search feedback..."/>
        </div>
        <button class="tb-btn" id="refreshBtn">&#8635; Refresh</button>
        <button class="tb-btn primary" onclick="location.href='my_csmr.php'">
          &#128196; Generate CSMR
        </button>
        <div class="tb-avatar" id="topbarAvatar">DA</div>
      </div>
    </div>

    <!-- Page content -->
    <div class="page-content">

      <!-- Live bar -->
      <div class="live-bar">
        <div class="live-dot" id="liveDot"></div>
        <span class="live-text">
          Live &nbsp;&middot;&nbsp; Last updated: <span id="lastUpdated">just now</span>
        </span>
        <span class="live-date" id="todayDate">Sunday, April 06, 2026</span>
      </div>

      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            &#128203;
          </div>
          <div class="stat-info">
            <div class="stat-label">Total Feedback</div>
            <div class="stat-value" id="totalFeedback">218</div>
            <div class="stat-change positive">&#9650; 14% from last month</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            &#128276;
          </div>
          <div class="stat-info">
            <div class="stat-label">Unread</div>
            <div class="stat-value" id="unreadFeedback">28</div>
            <div class="stat-change">Needs attention</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            &#11088;
          </div>
          <div class="stat-info">
            <div class="stat-label">Avg. Rating</div>
            <div class="stat-value" id="avgRating">4.6</div>
            <div class="stat-change positive">&#9650; 0.2 improvement</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            &#128197;
          </div>
          <div class="stat-info">
            <div class="stat-label">This Month</div>
            <div class="stat-value" id="monthlyFeedback">68</div>
            <div class="stat-change">Ongoing collection</div>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-left">
          <div class="filter-group">
            <label>Status:</label>
            <select id="filterStatus" class="filter-select">
              <option value="all">All Feedback</option>
              <option value="unread">Unread Only</option>
              <option value="read">Read Only</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Rating:</label>
            <select id="filterRating" class="filter-select">
              <option value="all">All Ratings</option>
              <option value="5">5 Stars ★★★★★</option>
              <option value="4">4 Stars ★★★★</option>
              <option value="3">3 Stars ★★★</option>
              <option value="2">2 Stars ★★</option>
              <option value="1">1 Star ★</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Period:</label>
            <select id="filterPeriod" class="filter-select">
              <option value="all">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="custom">Custom Range</option>
            </select>
          </div>

          <div class="filter-group date-range" id="customDateRange" style="display: none;">
            <input type="date" id="dateFrom" class="filter-input">
            <span>to</span>
            <input type="date" id="dateTo" class="filter-input">
          </div>
        </div>

        <div class="filter-right">
          <button class="filter-btn" id="applyFilters">Apply Filters</button>
          <button class="filter-btn secondary" id="clearFilters">Clear All</button>
        </div>
      </div>

      <!-- Quick Filter Chips -->
      <div class="chip-bar">
        <div class="chip active" data-filter="all">
          <span class="chip-icon">&#128203;</span> All Feedback
        </div>
        <div class="chip" data-filter="today">
          <span class="chip-icon">&#128197;</span> Today
        </div>
        <div class="chip" data-filter="week">
          <span class="chip-icon">&#128198;</span> This Week
        </div>
        <div class="chip" data-filter="unread">
          <span class="chip-icon">&#128276;</span> Unread (28)
        </div>
        <div class="chip" data-filter="high-rating">
          <span class="chip-icon">&#11088;</span> High Rated
        </div>
        <div class="chip" data-filter="low-rating">
          <span class="chip-icon">&#9888;</span> Low Rated
        </div>
      </div>

      <!-- Feedback List Container -->
      <div class="feedback-container">
        
        <!-- Feedback List Header -->
        <div class="feedback-header">
          <div class="header-left">
            <h2>Feedback Messages</h2>
            <span class="result-count">Showing <strong id="showingCount">10</strong> of <strong id="totalCount">218</strong> feedback</span>
          </div>
          <div class="header-right">
            <button class="icon-btn" id="markAllRead" title="Mark all as read">
              <span>&#10004;</span>
            </button>
            <button class="icon-btn" id="exportBtn" title="Export feedback">
              <span>&#128190;</span>
            </button>
            <div class="view-toggle">
              <button class="view-btn active" data-view="list">
                <span>&#9776;</span>
              </button>
              <button class="view-btn" data-view="grid">
                <span>&#9638;</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Feedback List -->
        <div class="feedback-list" id="feedbackList">
          
          <!-- Sample Feedback Item 1 - Unread -->
          <div class="feedback-item unread" data-id="1">
            <div class="fb-status">
              <div class="unread-dot"></div>
            </div>
            <div class="fb-content">
              <div class="fb-header">
                <div class="fb-meta">
                  <span class="fb-id">#218</span>
                  <span class="fb-date">&#128197; April 5, 2026 - 2:30 PM</span>
                  <span class="fb-type">&#128100; Citizen</span>
                </div>
                <div class="fb-rating excellent">
                  <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                  <span class="rating-value">5.0</span>
                </div>
              </div>
              <div class="fb-preview">
                The service was excellent! Staff were very accommodating and the process was quick. 
                I appreciate the effort of the office in making transactions easier for citizens...
              </div>
              <div class="fb-tags">
                <span class="tag sqd-high">High SQD Score</span>
                <span class="tag">Has Comments</span>
                <span class="tag">Citizen</span>
              </div>
              <div class="fb-actions">
                <button class="action-btn primary" onclick="viewFeedback(1)">
                  <span>&#128065;</span> View Details
                </button>
                <button class="action-btn" onclick="markAsRead(1)">
                  <span>&#10004;</span> Mark as Read
                </button>
              </div>
            </div>
          </div>

          <!-- Sample Feedback Item 2 - Read -->
          <div class="feedback-item" data-id="2">
            <div class="fb-status"></div>
            <div class="fb-content">
              <div class="fb-header">
                <div class="fb-meta">
                  <span class="fb-id">#217</span>
                  <span class="fb-date">&#128197; April 5, 2026 - 10:15 AM</span>
                  <span class="fb-type">&#128100; Business Owner</span>
                </div>
                <div class="fb-rating good">
                  <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
                  <span class="rating-value">4.0</span>
                </div>
              </div>
              <div class="fb-preview">
                Good service overall, but waiting time could be improved. The staff were helpful 
                but there were too few personnel during peak hours.
              </div>
              <div class="fb-tags">
                <span class="tag">Business Owner</span>
                <span class="tag">Has Comments</span>
              </div>
              <div class="fb-actions">
                <button class="action-btn primary" onclick="viewFeedback(2)">
                  <span>&#128065;</span> View Details
                </button>
              </div>
            </div>
          </div>

          <!-- Sample Feedback Item 3 - Unread with Low Rating -->
          <div class="feedback-item unread" data-id="3">
            <div class="fb-status">
              <div class="unread-dot"></div>
            </div>
            <div class="fb-content">
              <div class="fb-header">
                <div class="fb-meta">
                  <span class="fb-id">#216</span>
                  <span class="fb-date">&#128197; April 4, 2026 - 4:45 PM</span>
                  <span class="fb-type">&#128100; Citizen</span>
                </div>
                <div class="fb-rating poor">
                  <span class="stars">&#9733;&#9733;&#9734;&#9734;&#9734;</span>
                  <span class="rating-value">2.0</span>
                </div>
              </div>
              <div class="fb-preview">
                Very disappointed with the long waiting time and unclear instructions. 
                The staff seemed overwhelmed and not properly trained...
              </div>
              <div class="fb-tags">
                <span class="tag sqd-low">Low SQD Score</span>
                <span class="tag priority">Needs Attention</span>
                <span class="tag">Has Comments</span>
              </div>
              <div class="fb-actions">
                <button class="action-btn primary" onclick="viewFeedback(3)">
                  <span>&#128065;</span> View Details
                </button>
                <button class="action-btn" onclick="markAsRead(3)">
                  <span>&#10004;</span> Mark as Read
                </button>
              </div>
            </div>
          </div>

          <!-- Sample Feedback Item 4 - Read -->
          <div class="feedback-item" data-id="4">
            <div class="fb-status"></div>
            <div class="fb-content">
              <div class="fb-header">
                <div class="fb-meta">
                  <span class="fb-id">#215</span>
                  <span class="fb-date">&#128197; April 4, 2026 - 2:20 PM</span>
                  <span class="fb-type">&#128100; Senior Citizen</span>
                </div>
                <div class="fb-rating excellent">
                  <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                  <span class="rating-value">5.0</span>
                </div>
              </div>
              <div class="fb-preview">
                Very satisfied! The PWD/Senior lane was very helpful and staff assisted me properly.
                Thank you for making government services accessible to seniors.
              </div>
              <div class="fb-tags">
                <span class="tag">Senior Citizen</span>
                <span class="tag">PWD/Senior Lane</span>
                <span class="tag sqd-high">High SQD Score</span>
              </div>
              <div class="fb-actions">
                <button class="action-btn primary" onclick="viewFeedback(4)">
                  <span>&#128065;</span> View Details
                </button>
              </div>
            </div>
          </div>

          <!-- Sample Feedback Item 5 - Unread -->
          <div class="feedback-item unread" data-id="5">
            <div class="fb-status">
              <div class="unread-dot"></div>
            </div>
            <div class="fb-content">
              <div class="fb-header">
                <div class="fb-meta">
                  <span class="fb-id">#214</span>
                  <span class="fb-date">&#128197; April 4, 2026 - 11:00 AM</span>
                  <span class="fb-type">&#128100; Citizen</span>
                </div>
                <div class="fb-rating good">
                  <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
                  <span class="rating-value">4.5</span>
                </div>
              </div>
              <div class="fb-preview">
                The new QR feedback system is convenient. Service was good, just need more chairs 
                in the waiting area.
              </div>
              <div class="fb-tags">
                <span class="tag">Has Comments</span>
                <span class="tag">Via QR Code</span>
              </div>
              <div class="fb-actions">
                <button class="action-btn primary" onclick="viewFeedback(5)">
                  <span>&#128065;</span> View Details
                </button>
                <button class="action-btn" onclick="markAsRead(5)">
                  <span>&#10004;</span> Mark as Read
                </button>
              </div>
            </div>
          </div>

        </div>
        <!-- /feedback-list -->

        <!-- Pagination -->
        <div class="pagination">
          <button class="page-btn" id="prevPage" disabled>
            <span>&#9664;</span> Previous
          </button>
          <div class="page-numbers">
            <button class="page-num active">1</button>
            <button class="page-num">2</button>
            <button class="page-num">3</button>
            <span class="page-dots">...</span>
            <button class="page-num">22</button>
          </div>
          <button class="page-btn" id="nextPage">
            Next <span>&#9654;</span>
          </button>
        </div>

      </div>
      <!-- /feedback-container -->

    </div>
    <!-- /page-content -->

  </div>
  <!-- /main-area -->

</div>
<!-- /app-shell -->

<!-- ══════════════ FEEDBACK DETAIL MODAL ══════════════ -->
<div class="modal" id="feedbackModal">
  <div class="modal-overlay" onclick="closeModal()"></div>
  <div class="modal-content">
    
    <!-- Modal Header -->
    <div class="modal-header">
      <div>
        <h2>Feedback Details</h2>
        <p class="modal-subtitle">Feedback ID: <span id="modalFeedbackId">#218</span></p>
      </div>
      <button class="modal-close" onclick="closeModal()">
        <span>&#10005;</span>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="modal-body">
      
      <!-- Respondent Information -->
      <div class="detail-section">
        <h3 class="section-title">&#128100; Respondent Information</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Type</label>
            <div class="detail-value" id="modalRespondentType">Citizen</div>
          </div>
          <div class="detail-item">
            <label>Age</label>
            <div class="detail-value" id="modalAge">35 years old</div>
          </div>
          <div class="detail-item">
            <label>Gender</label>
            <div class="detail-value" id="modalGender">Male</div>
          </div>
          <div class="detail-item">
            <label>Date Submitted</label>
            <div class="detail-value" id="modalDate">April 5, 2026 - 2:30 PM</div>
          </div>
        </div>
      </div>

      <!-- Rating Overview -->
      <div class="detail-section">
        <h3 class="section-title">&#11088; Overall Rating</h3>
        <div class="rating-overview">
          <div class="rating-large">
            <div class="rating-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
            <div class="rating-number">5.0 / 5.0</div>
            <div class="rating-label excellent">Excellent</div>
          </div>
        </div>
      </div>

      <!-- SQD Breakdown -->
      <div class="detail-section">
        <h3 class="section-title">&#128200; Service Quality Dimensions (SQD)</h3>
        <div class="sqd-grid">
          <div class="sqd-card">
            <div class="sqd-label">CC1 - Responsiveness</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
          <div class="sqd-card">
            <div class="sqd-label">CC2 - Reliability</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
          <div class="sqd-card">
            <div class="sqd-label">CC3 - Access & Facilities</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
          <div class="sqd-card">
            <div class="sqd-label">CC4 - Communication</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
          <div class="sqd-card">
            <div class="sqd-label">CC5 - Costs</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
          <div class="sqd-card">
            <div class="sqd-label">CC6 - Integrity</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
          <div class="sqd-card">
            <div class="sqd-label">CC7 - Assurance</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
          <div class="sqd-card">
            <div class="sqd-label">CC8 - Outcome</div>
            <div class="sqd-score excellent">5</div>
            <div class="sqd-bar">
              <div class="sqd-fill" style="width: 100%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <div class="detail-section">
        <h3 class="section-title">&#128172; Comments & Suggestions</h3>
        <div class="comment-box" id="modalComments">
          The service was excellent! Staff were very accommodating and the process was quick. 
          I appreciate the effort of the office in making transactions easier for citizens. 
          The new QR feedback system is also very convenient. Keep up the good work!
        </div>
      </div>

    </div>
    <!-- /modal-body -->

    <!-- Modal Footer -->
    <div class="modal-footer">
      <button class="modal-btn secondary" onclick="closeModal()">Close</button>
      <button class="modal-btn" onclick="markAsRead()">
        <span>&#10004;</span> Mark as Read
      </button>
      <button class="modal-btn primary" onclick="window.print()">
        <span>&#128438;</span> Print Feedback
      </button>
    </div>

  </div>
</div>
<!-- /MODAL -->

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="../js/feedback_inbox.js"></script>

</body>
</html>