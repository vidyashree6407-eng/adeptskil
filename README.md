Adeptskil website

- Canonical courses page: `courses.html` — this repository uses `courses.html` as the single source of truth for the course catalog.
- `courses_fixed.html` was removed to avoid duplication; if you need a backup, use git history.

Notes:
- Course cards use icon placeholders (graduation-cap) for a consistent lightweight layout.
- If you want images restored, update `courses.html` and add image URLs inside `.course-image`.
- To preview locally: run `python -m http.server 8000` in the project root and open `http://localhost:8000/courses.html`.
