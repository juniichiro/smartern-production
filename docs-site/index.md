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
  - icon: 🎯
    title: Automated Placement (Rete)
    details: Leverages the Rete Matching Algorithm to analyze student resumes and match them to office requirements with high-performance rules.
  - icon: 🛡️
    title: Anomaly Detection (ML)
    details: Monitors system logs using an Isolation Forest model and a persistent Python daemon to identify suspicious patterns.
  - icon: 📍
    title: GPS Geofencing & MFA
    details: Restricts clock-ins to physical workspace coordinates and secures accounts with single-use TOTP multi-factor codes.
  - icon: 👥
    title: Multi-Role Dashboards
    details: Custom portals for Interns, Supervisors, Head Supervisors, School Coordinators, and Administrators.
---

<div class="developer-portal-home" v-pre>

## Developer Quick Access

Explore key architectural structures and codebase implementation paths.

<div class="bento-grid">
  <a href="/getting-started/installation" class="bento-card">
    <div class="bento-icon">⚙️</div>
    <h3>Getting Started</h3>
    <p>Setup the PHP environment, configure database migrations, and run testing components.</p>
  </a>

  <a href="/getting-started/security" class="bento-card">
    <div class="bento-icon">🔐</div>
    <h3>Security Controls</h3>
    <p>Verify how single-use TOTP MFA (code burn) and server-side GPS geofencing models operate.</p>
  </a>

  <a href="/core-concepts/rete-matching" class="bento-card">
    <div class="bento-icon">🧠</div>
    <h3>Matching Logic</h3>
    <p>Understand the rules, facts compilation, and background python process execution.</p>
  </a>

  <a href="/reference/controllers" class="bento-card">
    <div class="bento-icon">📂</div>
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
  font-size: 1.75rem;
  margin-bottom: 16px;
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
