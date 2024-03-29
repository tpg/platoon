import {defaultTheme} from "vuepress";

export default {
    lang: 'en-US',
    title: 'Platoon',
    description: 'Platoon deployment documentation',
    base: '/platoon/',
    theme: defaultTheme({
        repo: 'thepublicgood/platoon',
        navbar: [
            {
                text: 'Guide',
                link: '/guide/getting_started.md',
            },
            {
                text: 'Reference',
                children: ['/reference/config.md', '/reference/envoy.md']
            }
        ],
        sidebar: {
            '/guide/': [
                {
                    text: 'Guide',
                    children: ['/guide/getting_started.md', '/guide/releases.md'],
                },
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
