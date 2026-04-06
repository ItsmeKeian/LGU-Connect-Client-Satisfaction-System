$('#menuToggle').on('click', function () {
    $('#sidebar').toggleClass('sb-open');
});

/* ═══════════════════════════════════════════════════════
   FEEDBACK INBOX 
   ═══════════════════════════════════════════════════════ */

   $(document).ready(function() {
  
    // ────────── Initialize ────────── 
    initializePage();
    setupEventListeners();
    updateDateTime();
    setInterval(updateDateTime, 60000); // Update every minute
  
    // ────────── Functions ────────── 
  
    function initializePage() {
      console.log('Feedback Inbox initialized');
      
      // Set current date
      const today = new Date();
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      $('#todayDate').text(today.toLocaleDateString('en-US', options));
      
      // Initialize topbar avatar
      $('#topbarAvatar').text('DA');
      
      // Show/hide custom date range
      toggleCustomDateRange();
    }
  
    function setupEventListeners() {
      // Menu toggle for mobile
      $('#menuToggle').click(function() {
        $('#sidebar').toggleClass('active');
      });
  
      // Search functionality (debounced)
      let searchTimeout;
      $('#fbSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        searchTimeout = setTimeout(function() {
          searchFeedback(searchTerm);
        }, 500);
      });
  
      // Filter changes
      $('#filterStatus, #filterRating, #filterPeriod').change(function() {
        console.log('Filter changed:', $(this).attr('id'), $(this).val());
      });
  
      // Show custom date range when "Custom Range" is selected
      $('#filterPeriod').change(function() {
        toggleCustomDateRange();
      });
  
      // Apply filters button
      $('#applyFilters').click(function() {
        applyFilters();
      });
  
      // Clear filters button
      $('#clearFilters').click(function() {
        clearFilters();
      });
  
      // Refresh button
      $('#refreshBtn').click(function() {
        $(this).addClass('rotating');
        setTimeout(() => {
          $(this).removeClass('rotating');
          location.reload();
        }, 500);
      });
  
      // Quick filter chips
      $('.chip').click(function() {
        $('.chip').removeClass('active');
        $(this).addClass('active');
        const filter = $(this).data('filter');
        applyQuickFilter(filter);
      });
  
      // View toggle (list/grid)
      $('.view-btn').click(function() {
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        const view = $(this).data('view');
        toggleView(view);
      });
  
      // Mark all as read
      $('#markAllRead').click(function() {
        if(confirm('Mark all feedback as read?')) {
          markAllAsRead();
        }
      });
  
      // Export button
      $('#exportBtn').click(function() {
        exportFeedback();
      });
  
      // Pagination
      $('.page-num').click(function() {
        if(!$(this).hasClass('active')) {
          changePage($(this).text());
        }
      });
  
      $('#prevPage').click(function() {
        if(!$(this).is(':disabled')) {
          console.log('Previous page');
        }
      });
  
      $('#nextPage').click(function() {
        if(!$(this).is(':disabled')) {
          console.log('Next page');
        }
      });
  
      // Close modal when clicking overlay
      $('.modal-overlay').click(function() {
        closeModal();
      });
  
      // Close modal with ESC key
      $(document).keyup(function(e) {
        if (e.key === "Escape") {
          closeModal();
        }
      });
    }
  
    function updateDateTime() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
      $('#lastUpdated').text('just now');
      
      // Animate live dot
      $('#liveDot').fadeOut(500).fadeIn(500);
    }
  
    function toggleCustomDateRange() {
      const period = $('#filterPeriod').val();
      if(period === 'custom') {
        $('#customDateRange').slideDown(300);
      } else {
        $('#customDateRange').slideUp(300);
      }
    }
  
    function searchFeedback(searchTerm) {
      console.log('Searching for:', searchTerm);
      
      if(searchTerm === '') {
        // Show all feedback
        $('.feedback-item').show();
        updateResultCount();
        return;
      }
  
      // Simple frontend search (will be replaced with backend search)
      $('.feedback-item').each(function() {
        const text = $(this).text().toLowerCase();
        if(text.includes(searchTerm.toLowerCase())) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
  
      updateResultCount();
    }
  
    function applyFilters() {
      console.log('Applying filters...');
      const filters = {
        status: $('#filterStatus').val(),
        rating: $('#filterRating').val(),
        period: $('#filterPeriod').val(),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val()
      };
      
      console.log('Filter values:', filters);
      
      // Show success message
      showNotification('Filters applied successfully', 'success');
      
      // In production, this will make AJAX call to backend
      // For now, just log the filters
    }
  
    function clearFilters() {
      console.log('Clearing filters...');
      
      // Reset all filter inputs
      $('#filterStatus').val('all');
      $('#filterRating').val('all');
      $('#filterPeriod').val('all');
      $('#dateFrom').val('');
      $('#dateTo').val('');
      $('#fbSearch').val('');
      
      // Hide custom date range
      $('#customDateRange').slideUp(300);
      
      // Reset chips
      $('.chip').removeClass('active');
      $('.chip[data-filter="all"]').addClass('active');
      
      // Show all feedback items
      $('.feedback-item').show();
      
      updateResultCount();
      showNotification('Filters cleared', 'info');
    }
  
    function applyQuickFilter(filter) {
      console.log('Quick filter:', filter);
      
      // Simple frontend filtering (will be replaced with backend)
      switch(filter) {
        case 'all':
          $('.feedback-item').show();
          break;
        case 'today':
          // Filter by today's date
          $('.feedback-item').hide();
          $('.feedback-item:contains("April 6, 2026")').show();
          break;
        case 'week':
          // Filter by this week
          console.log('Filter: This week');
          break;
        case 'unread':
          $('.feedback-item').hide();
          $('.feedback-item.unread').show();
          break;
        case 'high-rating':
          $('.feedback-item').hide();
          $('.feedback-item:has(.fb-rating.excellent)').show();
          break;
        case 'low-rating':
          $('.feedback-item').hide();
          $('.feedback-item:has(.fb-rating.poor)').show();
          break;
      }
      
      updateResultCount();
    }
  
    function toggleView(view) {
      console.log('Switching to view:', view);
      
      if(view === 'grid') {
        $('.feedback-list').addClass('grid-view');
        showNotification('Grid view enabled', 'info');
      } else {
        $('.feedback-list').removeClass('grid-view');
        showNotification('List view enabled', 'info');
      }
    }
  
    function updateResultCount() {
      const visibleCount = $('.feedback-item:visible').length;
      const totalCount = $('.feedback-item').length;
      
      $('#showingCount').text(visibleCount);
      $('#totalCount').text(totalCount);
    }
  
    function changePage(pageNum) {
      console.log('Changing to page:', pageNum);
      
      $('.page-num').removeClass('active');
      $('.page-num').each(function() {
        if($(this).text() === pageNum) {
          $(this).addClass('active');
        }
      });
      
      // Scroll to top
      $('html, body').animate({ scrollTop: 0 }, 300);
      
      // In production, this will load new page data via AJAX
    }
  
    function markAllAsRead() {
      console.log('Marking all as read...');
      
      // Remove unread class and dot
      $('.feedback-item.unread').removeClass('unread');
      $('.unread-dot').remove();
      
      // Update counter
      $('#sbFeedbackCount').text('0');
      
      showNotification('All feedback marked as read', 'success');
      
      // In production, this will make AJAX call to backend
    }
  
    function exportFeedback() {
      console.log('Exporting feedback...');
      
      showNotification('Export feature coming soon...', 'info');
      
      // In production, this will trigger download of CSV/Excel file
    }
  
    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = $('<div class="notification ' + type + '">' + message + '</div>');
      
      // Add to page
      $('body').append(notification);
      
      // Show notification
      setTimeout(() => {
        notification.addClass('show');
      }, 100);
      
      // Hide and remove after 3 seconds
      setTimeout(() => {
        notification.removeClass('show');
        setTimeout(() => {
          notification.remove();
        }, 300);
      }, 3000);
    }
  
  });
  
  // ────────── Global Functions (called from HTML) ────────── 
  
  function viewFeedback(id) {
    console.log('Viewing feedback:', id);
    
    // In production, this will fetch data via AJAX
    // For now, just open the modal with sample data
    
    // Update modal content based on ID
    $('#modalFeedbackId').text('#' + id);
    
    // Show modal
    $('#feedbackModal').addClass('active');
    $('body').css('overflow', 'hidden');
    
    // Mark as read
    markAsRead(id);
  }
  
  function closeModal() {
    $('#feedbackModal').removeClass('active');
    $('body').css('overflow', 'auto');
  }
  
  function markAsRead(id) {
    console.log('Marking as read:', id);
    
    // Remove unread status from the item
    $('.feedback-item[data-id="' + id + '"]').removeClass('unread');
    $('.feedback-item[data-id="' + id + '"] .unread-dot').remove();
    
    // Update unread counter
    const unreadCount = $('.feedback-item.unread').length;
    $('#sbFeedbackCount').text(unreadCount);
    
    // In production, this will make AJAX call to backend
  }
  
  // ────────── CSS for Notifications ────────── 
  // 
  /*
  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 16px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    z-index: 10000;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  
  .notification.show {
    opacity: 1;
    transform: translateX(0);
  }
  
  .notification.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }
  
  .notification.info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  }
  
  .notification.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  }
  
  .notification.error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  }
  
  .rotating {
    animation: rotate 0.5s linear;
  }
  
  @keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  */