// sidebar_counts.js — include this on ALL admin pages
function loadSidebarCounts() {
    $.get('../php/get/get_feedback.php', { page: 1, per_page: 1 }, function(res) {
      if (res.success && res.summary) {
        const count = res.summary.total ?? 0;
        const badge = document.getElementById('sbFeedbackCount');
        if (badge) badge.textContent = count;
      }
    });
  }
  
  // Run on page load
  $(document).ready(function () {
    loadSidebarCounts();
  });