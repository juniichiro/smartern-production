---
layout: home

hero:
  name: "SmarTern"
  text: "Developer Documentation"
  tagline: "Secure, automated, and intelligent internship tracking developed for SDO QC."
  image:
    src: /images/blue-smartern.png
    alt: SmarTern Logo
  actions:
    - theme: brand
      text: Installation & Setup
      link: /getting-started/installation
    - theme: alt
      text: Codebase Reference
      link: /reference/controllers
    - theme: alt
      text: View on GitHub
      link: https://github.com/juniichiro/SmarTern

features:
  - icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'
    title: Automated Placement (Rete)
    details: Leverages the Rete Matching Algorithm to analyze student resumes and match them to office requirements with high-performance rules.
  - icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM12 8v4M12 16h.01"/></svg>'
    title: Anomaly Detection (ML)
    details: Monitors system logs using an Isolation Forest model and a persistent Python daemon to identify suspicious patterns.
  - icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>'
    title: GPS Geofencing & MFA
    details: Restricts clock-ins to physical workspace coordinates and secures accounts with single-use TOTP multi-factor codes.
  - icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>'
    title: Multi-Role Dashboards
    details: Custom portals for Interns, Supervisors, Head Supervisors, School Coordinators, and Administrators.
---

<div class="developer-portal-home" v-pre>

## Developer Quick Access

Explore key architectural structures and codebase implementation paths.

<div class="bento-grid">
  <a href="/getting-started/installation" class="bento-card">
    <div class="bento-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
    </div>
    <h3>Getting Started</h3>
    <p>Setup the PHP environment, configure database migrations, and run testing components.</p>
  </a>

  <a href="/getting-started/security" class="bento-card">
    <div class="bento-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
    </div>
    <h3>Security Controls</h3>
    <p>Verify how single-use TOTP MFA (code burn) and server-side GPS geofencing models operate.</p>
  </a>

  <a href="/core-concepts/rete-matching" class="bento-card">
    <div class="bento-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44 2.5 2.5 0 0 1 0-3.12 3 3 0 0 1 0-3.88 2.5 2.5 0 0 1 0-3.12A2.5 2.5 0 0 1 9.5 2zM14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44 2.5 2.5 0 0 0 0-3.12 3 3 0 0 0 0-3.88 2.5 2.5 0 0 0 0-3.12A2.5 2.5 0 0 0 14.5 2z"></path></svg>
    </div>
    <h3>Matching Logic</h3>
    <p>Understand the rules, facts compilation, and background python process execution.</p>
  </a>

  <a href="/reference/controllers" class="bento-card">
    <div class="bento-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
    </div>
    <h3>Code Catalog</h3>
    <p>Browse full indexes of database tables, middlewares, routes, and controllers.</p>
  </a>
</div>

</div>

<style>
.developer-portal-home {
  max-width: 1152px;
  margin: 0 auto;
  padding: 48px 24px;
}

.developer-portal-home h2 {
  font-size: 2rem;
  font-weight: 800;
  text-align: center;
  margin-bottom: 12px;
  letter-spacing: -0.025em;
}

.developer-portal-home p {
  text-align: center;
  color: var(--vp-c-text-2);
  margin-bottom: 40px;
}

.bento-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 24px;
}

@media (min-width: 640px) {
  .bento-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.bento-card {
  display: block;
  border: 1px solid var(--vp-c-bg-alt);
  background-color: var(--vp-c-bg-soft);
  border-radius: 12px;
  padding: 28px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  text-decoration: none !important;
}

.bento-card:hover {
  border-color: var(--vp-c-brand-1);
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -10px rgba(11, 86, 148, 0.15);
}

.bento-icon {
  color: var(--vp-c-brand-1);
  margin-bottom: 16px;
}

.dark .bento-icon {
  color: var(--vp-c-brand-2);
}

.bento-card h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--vp-c-text-1) !important;
  margin: 0 0 8px 0 !important;
}

.bento-card p {
  font-size: 0.95rem;
  color: var(--vp-c-text-2) !important;
  margin: 0;
  text-align: left;
  line-height: 1.5;
}
</style>
