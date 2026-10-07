The Basic Workflow
brainstorming - Activates before writing code. Refines rough ideas through questions, explores alternatives, presents design in sections for validation. Saves design document.

using-git-worktrees - Activates after design approval. Creates isolated workspace on new branch, runs project setup, verifies clean test baseline.

writing-plans - Activates with approved design. Breaks work into bite-sized tasks (2-5 minutes each). Every task has exact file paths, complete code, verification steps.

subagent-driven-development or executing-plans - Activates with plan. Either dispatches a fresh subagent per task with a review after each (most thorough), or implements every task inline in the current session with one fresh review of the whole branch at the end (cheapest).

test-driven-development - Activates during implementation. Enforces RED-GREEN-REFACTOR: write failing test, watch it fail, write minimal code, watch it pass, commit. Deletes code written before tests.

requesting-code-review - Activates between tasks. Reviews against plan, reports issues by severity. Critical issues block progress.

finishing-a-development-branch - Activates when tasks complete. Verifies tests, presents options (merge/PR/keep/discard), cleans up worktree.

---

## 🎨 UI/UX Senior Design Engineering Workflow & Directives

### 1. Visual Aesthetics & Design System (2026+ Next-Gen Generation Standards)
- **Zero-Boring / No Generic Palettes**: Pure basic colors are prohibited. Use sophisticated HSL-tailored color systems, dynamic gradients, dark/light harmonious tones, frosted glassmorphism (`backdrop-filter: blur()`), and subtle geometric math watermark accents.
- **Bilingual Typographic Harmony**: Pair modern Google Fonts (`Inter` for Latin and `Noto Sans Bengali` / `Hind Siliguri` for Bengali) with strict baseline alignment, comfortable line heights (`line-height: 1.6` for Bengali), and zero character clipping.
- **Component Elevation & Depth**: Subtle multi-layered drop shadows, crisp border strokes (`border: 1px solid rgba(255,255,255,0.12)` or soft border accents), and card hover elevations with micro-translations (`translateY(-4px)`).

### 2. Mobile Ergonomics & Micro-Interactions
- **Thumb-Zone Navigation**: Mobile-first sticky bottom action bar (`mobile-nav.php`), floating WhatsApp widget with glowing/pulsating aura, and sticky fast-action call buttons.
- **Frictionless Lead Funnels**: 30-60 second lead capture forms (Admission & Free Demo Class) with floating input labels, live client-side format checks (BD phone `01XXXXXXXXX`), async AJAX submission with button loading spinners, and celebratory feedback modals/toasts.
- **Interactive Feedback**: Instant hover effects, active click states, smooth accordion transitions for FAQs, and skeleton loading / shimmer states for dynamic data cards.

### 3. Mathematics-Centric Visual Identity & Trust Building
- **Trust Elements**: Teacher authority badges (Al Amin Sir's experience & achievements), live seat counter badges ("Only X seats left!"), batch schedule matrix with clear daytime indicators, and authentic student testimonials.
- **Digital Cards & Print-Ready Scorecards**: High-resolution, printable student ID cards with secure QR verification badges and clean merit lookup dashboards.

### 4. Accessibility & Cross-Device Resilience
- **A11y (WCAG 2.1 AA)**: Minimum 4.5:1 contrast ratio for body text, 3:1 for large headings and interactive UI controls. All buttons and links must have clear focus rings and `aria-label` tags.
- **Zero Layout Shifts (CLS < 0.1)**: Pre-allocated image dimensions and skeleton wrappers to ensure rock-solid visual stability during page load on 3G/4G networks.