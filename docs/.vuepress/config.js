const { defaultTheme } = require('@vuepress/theme-default')

module.exports = {
    lang: 'en-US',
    title: 'Platoon',
    description: 'Platoon deployment documentation',
    base: '/platoon/',
    theme: defaultTheme({
        repo: 'thepublicgood/platoon',
        navbar: [
            {
                text: 'Getting Started',
                link: '/guide',
            },
            {
                text: 'Reference',
                children: ['/reference/config.md', '/reference/envoy.md']
            }
        ],
        sidebar: {
            '/guide/': [
                {
                    text: 'Getting Started',
                    children: ['/guide/README.md'],
                }
            ],
            '/reference/': [
                {
                    text: 'Reference',
                    children: ['/reference/config.md', '/reference/envoy.md']
                }
            ]
        }
    }),
}