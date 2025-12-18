# Adeptskil Codebase Guide for AI Agents

## Project Overview
Adeptskil is a professional training and corporate education platform website. It's a static HTML/CSS/JavaScript site with a PHP backend for notification handling. The site showcases 200+ training courses across 12 categories and includes course enrollment and contact functionality.

## Architecture & Key Components

### Frontend Structure
- **Multi-page Site**: HTML files form the primary structure (index.html, courses.html, enrollment.html, about.html, contact.html, thank-you.html)
- **Courses Catalog**: \courses.html\ is the **canonical single source of truth** for course data — contains 3000+ lines with 200+ hardcoded course cards using \data-category\ attributes
- **Shared Styling**: \styles.css\ (2500+ lines) uses Flexbox, CSS Grid, CSS variables with responsive design (breakpoint: 768px)
- **Responsive Navigation**: Fixed navbar with hamburger menu on mobile, dropdown menus for course categories

### JavaScript Behavior
- **Navigation** (\script.js\, 614 lines): Mobile menu toggle, smooth scrolling for anchors with safety guards against malformed hrefs, navbar background change on scroll (rgba transition at 50px), dropdown menu handling via \.active\ class
- **Scroll Animation**: Intersection Observer pattern for fade-in animations on \.feature-card\, \.course-card\, \.testimonial-card\, \.stat-item\
- **Mobile Detection**: Uses \window.innerWidth <= 768\ for responsive behavior

### Backend Processing
- **Notification Handler**: \process_chatbot.php\ receives JSON POST from frontend
- **Notification Methods**: Email via PHP mail (default), SMS/WhatsApp via Twilio (commented out)
- **Error Logging**: Writes to \chatbot_errors.log\; error reporting disabled to prevent exposure

## Project-Specific Patterns

### Course Data Management
- Courses hardcoded in HTML with structure: \<div class="course-card" data-category="[category]">\
- Each card contains: category tag, title, description, "Enroll Now" button with \onclick="enrollCourse('Course Name')"\
- Course images: graduation-cap icon (\<i class="fas fa-graduation-cap"></i>\) - placeholder pattern for lightweight layout
- No database; edit \courses.html\ directly for course changes

### Enrollment Flow
- Button click  \enrollCourse()\ function (defined in page-specific JavaScript)
- Redirects to \enrollment.html\ (course name passed via session/URL)
- Form uses gradient background (#667eea  #764ba2)
- Form submission  redirect to \	hank-you.html\

### Navigation & Routing
- Fixed navbar (z-index: 1000) with minimal styling
- Mobile: Close menu on link click or outside click
- Smooth scroll guard: Checks for empty/malformed hrefs before \scrollIntoView({behavior: 'smooth'})\
- Dropdown toggles: Prevent default only on mobile (\window.innerWidth <= 768\)

### Styling Conventions
- Color scheme: Primary #667eea (blue), Secondary #764ba2 (purple)
- Font: System UI stack via CSS variable (no custom fonts)
- Responsive grid: \grid-template-columns: repeat(4, 1fr)\  stacks on mobile

## Critical Development Workflows

### Local Testing
\\\ash
python -m http.server 8000
# Open http://localhost:8000 in browser
\\\
- Must run from project root for relative paths to work
- Visit \index.html\, \courses.html\, \enrollment.html\ to test

### Course Updates
1. Edit \courses.html\ directly — add/remove course cards
2. Maintain \data-category\ attribute matching category headings (e.g., \data-category="leadership"\)
3. Update \enrollCourse()\ function if course structure changes (button onclick handler)
4. Ensure course name matches enrollment form expectations

### Form Integration
- Update email recipient in \process_chatbot.php\: change \\ = 'info@adeptskil.com'\
- Enrollment form: Check that \enrollCourse()\ properly passes course name to \enrollment.html\
- All messages logged to \chatbot_errors.log\ regardless of send success

## Integration Points & Dependencies

### External Libraries
- **Font Awesome 6.0.0**: CDN import for icons (\<i class="fas fa-*"></i>\)
- **No custom fonts**: Uses system fonts only

### Cross-Component Communication
- Course enrollment: \courses.html\ button  \enrollCourse()\ function  \enrollment.html\
- Navigation: \script.js\ manages all interactive states (mobile menu, dropdowns, smooth scroll)
- Backend: Frontend forms  \process_chatbot.php\ (JSON POST)  notifications

### SEO & Metadata
- Schema.org structured data in \<head>\ (\Organization\ schema)
- Open Graph tags for social sharing (og:title, og:image, og:url)
- Canonical URLs to prevent duplicate indexing

## Common Pitfalls & Conventions

1. **Don't duplicate course data** — Use \courses.html\ as single source of truth
2. **Check responsive breakpoint** — Always test mobile behavior with \window.innerWidth <= 768\
3. **Guard smooth scroll** — Check href validity before calling \scrollIntoView()\
4. **Maintain data-category consistency** — Course cards must match category IDs for filtering
5. **Icon placeholders only** — Course images are graduation caps; add image URLs only with asset pipeline

## File Dependencies
- **index.html**  \script.js\, \styles.css\
- **courses.html**  \script.js\, \styles.css\, Font Awesome
- **enrollment.html**  \script.js\, \styles.css\
- **script.js**  All pages; handles navigation and scroll behavior
- **process_chatbot.php**  Backend entry point for notifications
- **styles.css**  Single stylesheet for all pages
