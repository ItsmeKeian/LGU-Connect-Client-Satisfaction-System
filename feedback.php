<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
<title>LGU-Connect | Municipality of San Julian</title>
<link rel="icon" href="assets/img/logo.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/feedback.css"/>
</head>
<body>

<!-- ══ HEADER ══ -->
<div class="fb-header">
  <div class="fb-header-inner">
    <div class="fb-header-logo">
      <img src="assets/img/logo.png" alt="Logo" onerror="this.style.display='none'"/>
    </div>
    <div class="fb-header-text">
      <div class="fb-header-title" id="lguName">LGU-Connect</div>
      <div class="fb-header-sub"  id="lguSub">Client Satisfaction Feedback Form</div>
    </div>
  </div>
  <div class="fb-header-sub2" id="lguAddress">Loading…</div>

  <!-- ══ LANGUAGE TOGGLE (NEW) ══ -->
  <div class="fb-lang-toggle" id="langToggle">
    <button type="button" class="lang-btn active" data-lang="en">English</button>
    <button type="button" class="lang-btn" data-lang="tl">Tagalog</button>
  </div>
</div>

<!-- ══ MAIN CONTENT ══ -->
<div class="fb-container">

  <!-- Loading skeleton -->
  <div id="loadingWrap">
    <div class="fb-card">
      <div class="fb-card-body">
        <div class="skeleton" style="width:60%;height:18px;margin-bottom:12px"></div>
        <div class="skeleton" style="width:90%;margin-bottom:8px"></div>
        <div class="skeleton" style="width:75%"></div>
      </div>
    </div>
    <div class="fb-card">
      <div class="fb-card-body">
        <div class="skeleton" style="width:40%;height:18px;margin-bottom:12px"></div>
        <div class="skeleton" style="margin-bottom:8px"></div>
        <div class="skeleton" style="width:80%"></div>
      </div>
    </div>
  </div>

  <!-- Error state -->
  <div id="errorWrap" class="closed-wrap" style="display:none">
    <div class="closed-icon">⚠️</div>
    <h2>Something went wrong</h2>
    <p>Please refresh the page and try again.</p>
  </div>

  <!-- Closed state -->
  <div id="closedWrap" class="closed-wrap" style="display:none">
    <div class="closed-icon">🔒</div>
    <h2>Feedback Temporarily Closed</h2>
    <p>The feedback collection is currently not accepting submissions.<br>
       Please try again later or contact the office directly.</p>
  </div>

  <!-- ══ FORM (populated by feedback.js) ══ -->
  <div id="feedbackFormWrap" style="display:none">

    <!-- Dept banner — shown when dept is pre-selected via QR code -->
    <div id="deptBanner" class="dept-banner" style="display:none"></div>

    <form id="feedbackForm" novalidate>

      <!-- Hidden dept_code (when pre-selected via URL) -->
      <input type="hidden" id="deptCodeInput" name="dept_code" value=""/>

      <!-- ══ STEP 1: About You ══ -->
      <div class="fb-card">
        <div class="fb-card-header">
          <div class="step-badge">1</div>
          <div>
            <h3>About You</h3>
            <p>Your information helps us improve our services</p>
          </div>
        </div>
        <div class="fb-card-body">

          <!-- Dept dropdown injected by JS when no ?dept= in URL -->
          <div id="deptSelectWrap"></div>

          <!-- Respondent Type (UPDATED: Citizen / Business / Government — official ARTA client types) -->
          <div class="form-group" id="typeGroup">
            <label class="form-label">Client type <span class="required">*</span></label>
            <div class="radio-group">
              <input type="radio" class="radio-chip" name="respondent_type" id="type_citizen" value="citizen" checked>
              <label for="type_citizen">👤 Citizen</label>
              <input type="radio" class="radio-chip" name="respondent_type" id="type_business" value="business">
              <label for="type_business">🏪 Business</label>
              <input type="radio" class="radio-chip" name="respondent_type" id="type_government" value="government">
              <label for="type_government">🏛 Government (Employee or another agency)</label>
            </div>
          </div>

          <!-- Sex -->
          <div class="form-group" id="sexGroup">
            <label class="form-label">Sex <span class="required">*</span></label>
            <div class="radio-group">
              <input type="radio" class="radio-chip" name="sex" id="sex_male" value="male">
              <label for="sex_male">♂ Male</label>
              <input type="radio" class="radio-chip" name="sex" id="sex_female" value="female">
              <label for="sex_female">♀ Female</label>
              <input type="radio" class="radio-chip" name="sex" id="sex_prefer" value="prefer_not_to_say">
              <label for="sex_prefer">— Prefer not to say</label>
            </div>
            <div class="field-error">Please select your sex.</div>
          </div>

          <!-- Age Group -->
          <div class="form-group" id="ageGroup">
            <label class="form-label">Age Group <span class="required">*</span></label>
            <div class="radio-group">
              <input type="radio" class="radio-chip" name="age_group" id="age_1" value="below_18">
              <label for="age_1">Below 18</label>
              <input type="radio" class="radio-chip" name="age_group" id="age_2" value="18_30">
              <label for="age_2">18–30</label>
              <input type="radio" class="radio-chip" name="age_group" id="age_3" value="31_45">
              <label for="age_3">31–45</label>
              <input type="radio" class="radio-chip" name="age_group" id="age_4" value="46_60">
              <label for="age_4">46–60</label>
              <input type="radio" class="radio-chip" name="age_group" id="age_5" value="above_60">
              <label for="age_5">Above 60</label>
            </div>
            <div class="field-error">Please select your age group.</div>
          </div>

          <!-- Region of residence (NEW) -->
          <div class="form-group" id="regionGroup">
            <label class="form-label">Region of residence <span class="required">*</span></label>
            <input type="text" class="form-input" id="region" name="region"
              placeholder="e.g. Region VIII (Eastern Visayas)">
            <div class="field-error">Please enter your region of residence.</div>
          </div>

          <!-- Service Availed (NEW) -->
          <div class="form-group" id="serviceGroup">
            <label class="form-label">Service Availed <span class="required">*</span></label>
            <input type="text" class="form-input" id="service_availed" name="service_availed"
              placeholder="e.g. Business Permit Renewal">
            <div class="field-error">Please enter the service you availed.</div>
          </div>

        </div>
      </div>

      <!-- ══ STEP 2: Citizen's Charter (NEW) ══ -->
      <div class="fb-card">
        <div class="fb-card-header">
          <div class="step-badge">2</div>
          <div>
            <h3 id="ccStepTitle">Citizen's Charter</h3>
            <p id="ccStepSub">The Citizen's Charter is an official document listing an office's services, requirements, fees, and processing times.</p>
          </div>
        </div>
        <div class="fb-card-body" style="padding:16px 20px">
          <div id="ccContainer">
            <div class="skeleton" style="margin-bottom:20px;height:60px"></div>
            <div class="skeleton" style="height:60px"></div>
          </div>
        </div>
      </div>

      <!-- ══ STEP 3: Overall Rating ══ -->
      <div class="fb-card">
        <div class="fb-card-header">
          <div class="step-badge">3</div>
          <div>
            <h3>Overall Satisfaction</h3>
            <p>How would you rate your overall experience?</p>
          </div>
        </div>
        <div class="fb-card-body">
          <div class="form-group" id="ratingGroup">
            <div class="star-rating-wrap">
              <input type="radio" name="rating" id="star5" value="5"><label for="star5" title="Excellent">★</label>
              <input type="radio" name="rating" id="star4" value="4"><label for="star4" title="Good">★</label>
              <input type="radio" name="rating" id="star3" value="3"><label for="star3" title="Average">★</label>
              <input type="radio" name="rating" id="star2" value="2"><label for="star2" title="Poor">★</label>
              <input type="radio" name="rating" id="star1" value="1"><label for="star1" title="Very Poor">★</label>
            </div>
            <div class="overall-label" id="overallLabel">Tap a star to rate</div>
            <div class="field-error">Please give an overall rating.</div>
          </div>
          <div class="scale-hint">
            <span>1 – Very Poor</span>
            <span>3 – Average</span>
            <span>5 – Excellent</span>
          </div>
        </div>
      </div>

      <!-- ══ STEP 4: SQD Questions (injected by feedback.js) ══ -->
      <div class="fb-card">
        <div class="fb-card-header">
          <div class="step-badge">4</div>
          <div>
            <h3 id="sqdStepTitle">Service Quality Dimensions</h3>
            <p id="sqdStepSub">For each statement, choose the answer that best fits your experience.</p>
          </div>
        </div>
        <div class="fb-card-body" style="padding:16px 20px">
          <div id="sqdContainer">
            <div class="skeleton" style="margin-bottom:20px;height:60px"></div>
            <div class="skeleton" style="margin-bottom:20px;height:60px"></div>
            <div class="skeleton" style="height:60px"></div>
          </div>
        </div>
      </div>

      <!-- ══ STEP 5: Comments ══ -->
      <div class="fb-card">
        <div class="fb-card-header">
          <div class="step-badge">5</div>
          <div>
            <h3>Comments &amp; Suggestions</h3>
            <p>Optional — help us serve you better</p>
          </div>
        </div>
        <div class="fb-card-body">
          <div class="form-group">
            <label class="form-label">Comments / Remarks</label>
            <textarea class="form-textarea" name="comment" id="comment"
              placeholder="Share your experience with us…" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Suggestions for Improvement</label>
            <textarea class="form-textarea" name="suggestions" id="suggestions"
              placeholder="How can we improve our service?…" rows="3"></textarea>
          </div>
          <!-- Email (NEW, optional) -->
          <div class="form-group">
            <label class="form-label">Email address (optional)</label>
            <input type="email" class="form-input" id="email" name="email"
              placeholder="you@example.com">
          </div>
        </div>
      </div>

      <!-- ══ SUBMIT ══ -->
      <button type="submit" class="btn-submit" id="submitBtn">
        ✓ Submit Feedback
      </button>

      <div class="fb-footer">
        Your feedback is confidential and will only be used to improve public services.<br>
        This form complies with Republic Act No. 11032 (Ease of Doing Business Act).
      </div>

    </form>
  </div><!-- /feedbackFormWrap -->

</div><!-- /fb-container -->

<!-- ══ SUCCESS OVERLAY ══ -->
<div class="success-overlay" id="successOverlay">
  <div class="success-card">
    <div class="success-icon">✓</div>
    <h2>Thank You!</h2>
    <p>Your feedback has been successfully submitted.<br>
       It will help us improve our services for everyone.</p>
    <div class="success-meta">
      <strong id="successDept"></strong>
      <span id="successRating" style="color:#c8991a;font-size:18px;letter-spacing:2px"></span>
    </div>
    <button class="btn-another" onclick="submitAnother()">
      Submit Another Feedback
    </button>
  </div>
</div>

<!-- jQuery + feedback JS -->
<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/feedback.js"></script>

</body>
</html>