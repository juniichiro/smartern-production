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
        text: 'Getting Started',
        items: [
          { text: 'Installation & Setup', link: '/getting-started/installation' },
          { text: 'Security & MFA', link: '/getting-started/security' }
        ]
      },
      {
        text: 'Core Concepts',
        items: [
          { text: 'System Architecture', link: '/core-concepts/architecture' },
          { text: 'Automated Placement (Rete)', link: '/core-concepts/rete-matching' },
          { text: 'Anomaly Detection (ML)', link: '/core-concepts/anomaly-detection' }
        ]
      },
      {
        text: 'User Guides',
        items: [
          { text: 'Administrator Portal', link: '/guides/admin' },
          { text: 'Supervisors & Heads', link: '/guides/supervisors' },
          { text: 'Interns & Coordinators', link: '/guides/interns-coordinators' }
        ]
      },
      {
        text: 'Reference & Codebase',
        items: [
          { text: 'Environment Config', link: '/reference/environment' },
          { text: 'Testing (Pest)', link: '/reference/testing' },
          { text: 'Controllers Catalog', link: '/reference/controllers' },
          { text: 'Models & Schemas', link: '/reference/models' },
          { text: 'Middlewares & Services', link: '/reference/middleware-services' },
          { text: 'Routes & Python Scripts', link: '/reference/routes-scripts' },
          { text: 'Console Commands', link: '/reference/console-commands' },
          { text: 'Blade Views & Layouts', link: '/reference/views' },
          { text: 'AlpineJS & Javascript', link: '/reference/javascript' },
          { text: 'Packages & Dependencies', link: '/reference/dependencies' },
          { text: 'Security Implementations', link: '/reference/security-practices' }
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
