function loadSidebarCounts() {
  $.get('../php/get/get_counts.php', function(res) {
    if (!res.success) return;

    const badge = document.getElementById('sbFeedbackCount');
    if (badge) badge.textContent = res.feedback_total;

    // You can add more badges here later
    // e.g., const todayBadge = document.getElementById('sbTodayCount');
    // if (todayBadge) todayBadge.textContent = res.feedback_today;
  });
}

$(document).ready(function () {
  loadSidebarCounts();

  // Optional: auto-refresh every 60 seconds
  setInterval(loadSidebarCounts, 60000);
});


// ── Live Bar Date (runs on all pages) ──
(function () {
  const el = document.getElementById('todayDate');
  if (el) {
    el.textContent = new Date().toLocaleDateString('en-PH', {
      weekday: 'long', year: 'numeric',
      month: 'long',   day: 'numeric'
    });
  }
})();