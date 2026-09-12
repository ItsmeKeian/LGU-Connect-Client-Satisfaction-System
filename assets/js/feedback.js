// ══════════════════════════════════════════
// Static translation strings for UI chrome that
// doesn't come from the config file (headers, hints).
// SQD/CC question text itself comes from the server
// (php/config/csm_questions.php) based on ?lang=.
// ══════════════════════════════════════════
const UI_TEXT = {
  en: {
    ccStepTitle: "Citizen's Charter",
    ccStepSub:   "The Citizen's Charter is an official document listing an office's services, requirements, fees, and processing times.",
    sqdStepTitle: "Service Quality Dimensions",
    sqdStepSub:   "For each statement, choose the answer that best fits your experience.",
    scaleLabels: ['Strongly Disagree', 'Disagree', 'Neither Agree nor Disagree', 'Agree', 'Strongly Agree'],
    naLabel: 'N/A',
    pleaseRate: 'Please rate this statement.',
    pleaseSelect: 'Please select an answer.',
  },
  tl: {
    ccStepTitle: "Karta ng Mamamayan (Citizen's Charter)",
    ccStepSub:   "Ang Citizen's Charter ay isang opisyal na dokumento na naglalaman ng mga serbisyo, kinakailangan, bayarin, at oras ng pagproseso ng isang opisina.",
    sqdStepTitle: "Kalidad ng Serbisyo",
    sqdStepSub:   "Para sa bawat pahayag, piliin ang sagot na pinakaangkop sa iyong karanasan.",
    scaleLabels: ['Lubos na hindi sumasangayon', 'Hindi sumasangayon', 'Walang kinikilingan', 'Sumasangayon', 'Labis na sumasangayon'],
    naLabel: 'N/A',
    pleaseRate: 'Mangyaring sagutan ang pahayag na ito.',
    pleaseSelect: 'Mangyaring pumili ng sagot.',
  }
};

// ── Rating labels (overall star rating — unchanged, own scale) ──
const RATING_LABELS = {
  1: '😞 Very Poor',
  2: '😐 Poor',
  3: '🙂 Average',
  4: '😊 Good',
  5: '🤩 Excellent!'
};

// ── Get dept code from URL ──
const urlParams = new URLSearchParams(window.location.search);
const deptCode  = urlParams.get('dept') || '';

// ── Current language state (default English) ──
let currentLang = urlParams.get('lang') || 'en';
if (!['en', 'tl'].includes(currentLang)) currentLang = 'en';

// ── Init on page load ──
$(document).ready(function () {
  setActiveLangButton(currentLang);
  loadFormData();
  bindLangToggle();
});

// ══════════════════════════════════════════
// 0. Language toggle
// ══════════════════════════════════════════
function bindLangToggle() {
  $('.lang-btn').on('click', function () {
    const lang = $(this).data('lang');
    if (lang === currentLang) return;

    currentLang = lang;
    setActiveLangButton(lang);

    // Re-fetch form data in the new language.
    // NOTE: this resets any answers already filled in — form is
    // simple enough that this is acceptable, but flag to your
    // client if they want answers preserved across a language switch.
    $('#feedbackFormWrap').hide();
    $('#loadingWrap').show();
    loadFormData();
  });
}

function setActiveLangButton(lang) {
  $('.lang-btn').removeClass('active');
  $(`.lang-btn[data-lang="${lang}"]`).addClass('active');
}

// ══════════════════════════════════════════
// 1. Load all form data via AJAX
// ══════════════════════════════════════════
function loadFormData() {
  $.ajax({
    url:      'php/get/get_feedback_init.php',
    method:   'GET',
    data:     { dept: deptCode, lang: currentLang },
    dataType: 'json',
    success(res) {
      if (!res.success) {
        showError('Failed to load form. Please refresh the page.');
        return;
      }

      // Update header with LGU info
      $('#lguName').text(res.lgu.name);
      $('#lguSub').text('Client Satisfaction Feedback Form');
      $('#lguAddress').text(res.lgu.address + ' · Anti-Red Tape Authority (ARTA) Compliant');

      // Feedback closed?
      if (!res.is_open) {
        showClosedState();
        return;
      }

      // Show form
      buildForm(res);
      bindEvents();
    },
    error() {
      showError('Connection error. Please check your network and refresh.');
    }
  });
}

// ══════════════════════════════════════════
// 2. Build the form HTML from JSON data
// ══════════════════════════════════════════
function buildForm(res) {
  const { department, all_depts, sqd_questions, cc_questions, lang } = res;
  const t = UI_TEXT[lang] || UI_TEXT.en;

  // ── Static step text (CC + SQD headers) ──
  $('#ccStepTitle').text(t.ccStepTitle);
  $('#ccStepSub').text(t.ccStepSub);
  $('#sqdStepTitle').text(t.sqdStepTitle);
  $('#sqdStepSub').text(t.sqdStepSub);

  // ── Department banner ──
  if (department) {
    $('#deptBanner').html(`
      <div class="dept-icon">🏢</div>
      <div class="dept-info">
        <h3>${escHtml(department.name)}</h3>
        <p>You are submitting feedback for this office.
           ${department.head ? '<br>Officer-in-Charge: ' + escHtml(department.head) : ''}
        </p>
      </div>
    `).show();
    // Hidden dept_code input
    $('#deptCodeInput').val(department.code);
  } else {
    // Show department dropdown
    let opts = '<option value="">— Select the office you visited —</option>';
    all_depts.forEach(d => {
      opts += `<option value="${escAttr(d.code)}">${escHtml(d.name)}</option>`;
    });
    $('#deptSelectWrap').html(`
      <div class="form-group" id="deptGroup">
        <label class="form-label">Department / Office <span class="required">*</span></label>
        <select class="form-select" id="deptSelect" name="dept_code" required>
          ${opts}
        </select>
        <div class="field-error">Please select a department.</div>
      </div>
    `).show();
  }

  // ── Citizen's Charter (CC1-3) ──
  buildCCSection(cc_questions, t);

  // ── SQD Questions ──
  let sqdHtml = '';
  sqd_questions.forEach((sqd, idx) => {
    sqdHtml += `
      <div class="sqd-item" id="${sqd.key}_group">
        <div class="sqd-question">
          <span class="sqd-num">SQD${idx}</span>
          ${escHtml(sqd.question)}
        </div>
        <div class="sqd-scale-options">
          ${buildScaleOptions(sqd.key, t)}
        </div>
        <div class="field-error" id="${sqd.key}_error">${t.pleaseRate}</div>
      </div>`;
  });
  $('#sqdContainer').html(sqdHtml);

  // Show the form
  $('#feedbackFormWrap').show();
  $('#loadingWrap').hide();
}

// ── Build the CC1-3 section, with CC2/CC3 conditional on CC1 ──
function buildCCSection(cc_questions, t) {
  if (!cc_questions || !cc_questions.length) {
    $('#ccContainer').html('');
    return;
  }

  let html = '';
  cc_questions.forEach(cc => {
    // CC2 and CC3 start hidden — shown only if CC1 answered 1, 2, or 3.
    const hiddenClass = (cc.key === 'cc2' || cc.key === 'cc3') ? ' cc-conditional" style="display:none' : '';
    html += `
      <div class="cc-item" id="${cc.key}_group" style="margin-bottom:24px">
        <div class="sqd-question">
          <span class="sqd-num">${cc.key.toUpperCase()}</span>
          ${escHtml(cc.question)}
        </div>
        <div class="radio-group" id="${cc.key}_options">
          ${buildCCOptions(cc.key, cc.options)}
        </div>
        <div class="field-error" id="${cc.key}_error">${t.pleaseSelect}</div>
      </div>`;
  });
  $('#ccContainer').html(html);

  // Apply hidden state properly (can't easily inline the class+style combo above safely)
  $('#cc2_group, #cc3_group').hide().addClass('cc-conditional');

  // ── CC1 change handler: show/hide CC2 & CC3 ──
  $(document).off('change', 'input[name="cc1"]').on('change', 'input[name="cc1"]', function () {
    const val = parseInt(this.value);
    const awareOfCC = val >= 1 && val <= 3;

    if (awareOfCC) {
      $('#cc2_group, #cc3_group').show();
      // Clear any auto-set N/A values so the citizen must actually answer
      $('#cc2_options input, #cc3_options input').prop('checked', false);
    } else {
      // CC1 = 4 (not aware) → CC2/CC3 auto-answered as N/A, hidden from view
      $('#cc2_group, #cc3_group').hide();
      autoSetNA('cc2');
      autoSetNA('cc3');
    }
    $('#cc1_group').removeClass('has-error');
  });
}

// Auto-check the "Not Applicable" option for a CC question
// (last option in its options list) when CC1 = 4.
function autoSetNA(ccKey) {
  const inputs = $(`#${ccKey}_options input[type="radio"]`);
  if (inputs.length) {
    inputs.last().prop('checked', true);
  }
  $(`#${ccKey}_group`).removeClass('has-error');
}

// ── Build radio-chip options for a CC question (values = option numbers) ──
function buildCCOptions(ccKey, options) {
  let html = '';
  Object.keys(options).forEach(optNum => {
    const id = `${ccKey}_${optNum}`;
    html += `
      <input type="radio" class="radio-chip" name="${ccKey}" id="${id}" value="${optNum}">
      <label for="${id}">${escHtml(options[optNum])}</label>`;
  });
  return html;
}

// ── Build the 6-option ARTA scale (5-point Likert + N/A) for one SQD item ──
function buildScaleOptions(name, t) {
  let html = '';
  for (let v = 1; v <= 5; v++) {
    const id = `${name}_${v}`;
    html += `
      <input type="radio" class="radio-chip scale-chip" name="${name}" id="${id}" value="${v}">
      <label for="${id}">${v} – ${t.scaleLabels[v - 1]}</label>`;
  }
  // N/A option — submitted as 'na', converted to NULL server-side
  html += `
    <input type="radio" class="radio-chip scale-chip scale-chip-na" name="${name}" id="${name}_na" value="na">
    <label for="${name}_na">${t.naLabel}</label>`;
  return html;
}

// ══════════════════════════════════════════
// 3. Bind all events
// ══════════════════════════════════════════
// Guard flag — bindEvents() gets called again every time loadFormData()
// succeeds (initial load AND every language switch). Without this guard,
// the submit handler stacks up each time and causes duplicate inserts
// (e.g. switch language once → form submits twice).
let eventsAlreadyBound = false;

function bindEvents() {
  if (eventsAlreadyBound) return;
  eventsAlreadyBound = true;

  // Overall rating label update
  $(document).on('change', 'input[name="rating"]', function () {
    const lbl   = $('#overallLabel');
    const val   = parseInt(this.value);
    const color = val >= 4 ? '#1e7c3b' : val === 3 ? '#b06c10' : '#c0392b';
    lbl.text(RATING_LABELS[val] || 'Tap a star to rate').css('color', color);
    $('#ratingGroup').removeClass('has-error');
  });

  // Clear errors on interaction
  $(document).on('change', 'input[type="radio"]', function () {
    $(this).closest('.form-group, .sqd-item, .cc-item').removeClass('has-error');
  });
  $(document).on('change', 'select, input[type="text"], input[type="email"]', function () {
    $(this).closest('.form-group').removeClass('has-error');
  });

  // Form submit
  $('#feedbackForm').on('submit', function (e) {
    e.preventDefault();
    if (validateForm()) submitForm();
  });
}

// ══════════════════════════════════════════
// 4. Validate form
// ══════════════════════════════════════════
function validateForm() {
  let valid = true;

  // Department dropdown (if visible)
  const deptSel = $('#deptSelect');
  if (deptSel.length && !deptSel.val()) {
    $('#deptGroup').addClass('has-error');
    valid = false;
  }

  // Sex
  if (!$('input[name="sex"]:checked').length) {
    $('#sexGroup').addClass('has-error');
    valid = false;
  }

  // Age group
  if (!$('input[name="age_group"]:checked').length) {
    $('#ageGroup').addClass('has-error');
    valid = false;
  }

  // Region / Service Availed (required text fields)
  if (!$('#region').val().trim()) {
    $('#regionGroup').addClass('has-error');
    valid = false;
  }
  if (!$('#service_availed').val().trim()) {
    $('#serviceGroup').addClass('has-error');
    valid = false;
  }

  // CC1 always required
  if (!$('input[name="cc1"]:checked').length) {
    $('#cc1_group').addClass('has-error');
    valid = false;
  }
  // CC2/CC3 required only if visible (i.e. CC1 = 1-3); if hidden they were auto-set to N/A
  if ($('#cc2_group').is(':visible') && !$('input[name="cc2"]:checked').length) {
    $('#cc2_group').addClass('has-error');
    valid = false;
  }
  if ($('#cc3_group').is(':visible') && !$('input[name="cc3"]:checked').length) {
    $('#cc3_group').addClass('has-error');
    valid = false;
  }

  // Overall rating
  if (!$('input[name="rating"]:checked').length) {
    $('#ratingGroup').addClass('has-error');
    valid = false;
  }

  // SQD questions (N/A counts as answered)
  for (let i = 0; i <= 8; i++) {
    const key = `sqd${i}`;
    if (!$(`input[name="${key}"]:checked`).length) {
      $(`#${key}_group`).addClass('has-error');
      valid = false;
    }
  }

  // Scroll to first error
  if (!valid) {
    const firstErr = $('.has-error').first();
    if (firstErr.length) {
      $('html, body').animate({
        scrollTop: firstErr.offset().top - 100
      }, 400);
    }
  }

  return valid;
}

// ══════════════════════════════════════════
// 5. Submit form via AJAX
// ══════════════════════════════════════════
function submitForm() {
  const btn = $('#submitBtn');
  btn.prop('disabled', true).html('<span class="spinner-inline"></span> Submitting…');

  // Build payload
  const payload = {
    dept_code:       $('#deptCodeInput').val() || $('#deptSelect').val(),
    respondent_type: $('input[name="respondent_type"]:checked').val(),
    sex:             $('input[name="sex"]:checked').val(),
    age_group:       $('input[name="age_group"]:checked').val(),
    region:          $('#region').val().trim(),
    service_availed: $('#service_availed').val().trim(),
    rating:          $('input[name="rating"]:checked').val(),
    comment:         $('#comment').val().trim(),
    suggestions:     $('#suggestions').val().trim(),
    email:           $('#email').val().trim(),
    language:        currentLang,
    cc1:             $('input[name="cc1"]:checked').val(),
    cc2:             $('input[name="cc2"]:checked').val() || '',
    cc3:             $('input[name="cc3"]:checked').val() || '',
  };

  // SQD scores — 'na' is sent as-is; submit_feedback.php converts it to NULL
  for (let i = 0; i <= 8; i++) {
    payload[`sqd${i}`] = $(`input[name="sqd${i}"]:checked`).val();
  }

  $.ajax({
    url:      'php/save/submit_feedback.php',
    method:   'POST',
    data:     payload,
    dataType: 'json',
    success(res) {
      if (res.success) {
        showSuccess(res.dept_name || payload.dept_code, payload.rating);
      } else {
        alert('Error: ' + (res.message || 'Please try again.'));
        btn.prop('disabled', false).html('✓ Submit Feedback');
      }
    },
    error() {
      alert('Network error. Please check your connection and try again.');
      btn.prop('disabled', false).html('✓ Submit Feedback');
    }
  });
}

// ══════════════════════════════════════════
// 6. Success overlay
// ══════════════════════════════════════════
function showSuccess(deptName, rating) {
  const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
  $('#successDept').text(deptName);
  $('#successRating').html(`${stars} (${rating}/5)`);
  $('#successOverlay').addClass('show');
}

function submitAnother() {
  $('#successOverlay').removeClass('show');
  $('#feedbackForm')[0].reset();
  $('#overallLabel').text('Tap a star to rate').css('color', '#555');
  $('#cc2_group, #cc3_group').hide();
  $('#submitBtn').prop('disabled', false).html('✓ Submit Feedback');
  $('html, body').animate({ scrollTop: 0 }, 400);
}

// ══════════════════════════════════════════
// 7. Special states
// ══════════════════════════════════════════
function showClosedState() {
  $('#loadingWrap').hide();
  $('#feedbackFormWrap').hide();
  $('#closedWrap').show();
}

function showError(msg) {
  $('#loadingWrap').hide();
  $('#errorWrap').text(msg).show();
}

// ══════════════════════════════════════════
// 8. Helpers
// ══════════════════════════════════════════
function escHtml(s) {
  if (!s) return '';
  return String(s)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function escAttr(s) {
  if (!s) return '';
  return String(s).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}