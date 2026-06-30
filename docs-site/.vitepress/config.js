import { defineConfig } from 'vitepress'

export default defineConfig({
  base: '/admin/docs/',
  title: "SmarTern Docs",
  description: "Specialized Internship Management Platform for SDO QC",
  srcDir: '.',
  outDir: './dist',
  themeConfig: {
    logo: '/images/blue-smartern.png',
    siteTitle: 'SmarTern Docs',
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Guide', link: '/getting-started/installation' },
      { text: 'Reference', link: '/reference/environment' }
    ],
    sidebar: [
      {
        text: '1. Getting Started',
        items: [
          { text: 'Installation & Setup', link: '/getting-started/installation' },
          { text: 'System Architecture', link: '/core-concepts/architecture' },
          { text: 'Environment Configuration', link: '/reference/environment' },
          { text: 'Packages & Dependencies', link: '/reference/dependencies' }
        ]
      },
      {
        text: '2. Portal User Guides',
        items: [
          { text: 'Administrator Portal', link: '/guides/admin' },
          { text: 'Supervisors & Heads', link: '/guides/supervisors' },
          { text: 'Interns & Coordinators', link: '/guides/interns-coordinators' }
        ]
      },
      {
        text: '3. Core Intelligent Features',
        items: [
          { text: 'Automated Placement (Rete)', link: '/core-concepts/rete-matching' },
          { text: 'Anomaly Detection (ML)', link: '/core-concepts/anomaly-detection' },
          { text: 'Security Implementations', link: '/reference/security-practices' },
          { text: 'GPS Geofencing & MFA', link: '/getting-started/security' }
        ]
      },
      {
        text: '4. Backend Codebase Reference',
        items: [
          { text: 'Controllers Catalog', link: '/reference/controllers' },
          { text: 'Models & Database Schemas', link: '/reference/models' },
          { text: 'Middlewares & Services', link: '/reference/middleware-services' },
          { text: 'Routes & Python Scripts', link: '/reference/routes-scripts' },
          { text: 'Artisan Console Commands', link: '/reference/console-commands' },
          { text: 'Cookies, Sessions & Cache', link: '/reference/cookies-session-cache' }
        ]
      },
      {
        text: '5. Frontend Codebase Reference',
        items: [
          { text: 'Blade Views & Layouts', link: '/reference/views' },
          { text: 'AlpineJS & Javascript', link: '/reference/javascript' },
          { text: 'Pest Testing Suite', link: '/reference/testing' }
        ]
      }
    ],
    socialLinks: [
      { icon: 'github', link: 'https://github.com/juniichiro/SmarTern' }
    ],
    search: {
      provider: 'local'
    },
    footer: {
      message: 'Developed for the Schools Division Office of Quezon City (SDO QC).',
      copyright: 'Copyright © 2026 SmarTern Team'
    },
    editLink: {
      pattern: 'https://github.com/juniichiro/SmarTern/edit/main/docs-site/:path',
      text: 'Edit this page on GitHub'
    }
  }
})
