// ============================================================
// shared.js — Used by ALL admin pages
// ============================================================

// ── Today's date ──
document.getElementById('todayDate').textContent =
  new Date().toLocaleDateString('en-PH', {
    weekday: 'long', year: 'numeric',
    month: 'long',  day: 'numeric'
  });

// ── Sidebar toggle with overlay ──
const sidebar    = document.getElementById('sidebar');
const menuToggle = document.getElementById('menuToggle');

// Create overlay element dynamically
const overlay = document.createElement('div');
overlay.id = 'sidebarOverlay';
document.body.appendChild(overlay);

function openSidebar() {
  sidebar.classList.add('sb-open');
  overlay.classList.add('active');
}

function closeSidebar() {
  sidebar.classList.remove('sb-open');
  overlay.classList.remove('active');
}

// Toggle on hamburger click
menuToggle.addEventListener('click', function() {
  sidebar.classList.contains('sb-open') ? closeSidebar() : openSidebar();
});

// Close when clicking overlay (outside sidebar)
overlay.addEventListener('click', closeSidebar);

// ── Avatar dropdown ──
function toggleAvatarDropdown(e) {
  e.stopPropagation();
  document.getElementById('avatarDropdown').classList.toggle('show');
}

document.addEventListener('click', function() {
  document.getElementById('avatarDropdown').classList.remove('show');
});

document.getElementById('avatarDropdown')
  .addEventListener('click', function(e) {
    e.stopPropagation();
  });