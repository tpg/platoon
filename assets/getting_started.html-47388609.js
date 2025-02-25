import{_ as l,r as a,o as d,c as s,a as o,b as e,d as n,w as c,e as t}from"./app-e92ff216.js";const u={},h=t(`<p>Platoon is a simple deployment solution for Laravel. It&#39;s based around Laravel Envoy and can help create zero-downtime deployments without needing fancy hosted solutions.</p><h2 id="installation" tabindex="-1"><a class="header-anchor" href="#installation" aria-hidden="true">#</a> Installation</h2><p>Like everything Laravel, Platoon is installed using Composer:</p><div class="language-bash line-numbers-mode" data-ext="sh"><pre class="language-bash"><code><span class="token function">composer</span> require thepublicgood/platoon
</code></pre><div class="line-numbers" aria-hidden="true"><div class="line-number"></div></div></div><p>Once installed, run the <code>platoon:publish</code> command to place the <code>platoon.php</code> config file in your <code>config</code> directory:</p><div class="language-bash line-numbers-mode" data-ext="sh"><pre class="language-bash"><code>php ./artisan platoon:publish
</code></pre><div class="line-numbers" aria-hidden="true"><div class="line-number"></div></div></div>`,6),p={href:"/reference/config.html",target:"_blank",rel:"noopener noreferrer"},y=t('<h2 id="server-configuration" tabindex="-1"><a class="header-anchor" href="#server-configuration" aria-hidden="true">#</a> Server Configuration</h2><p>You&#39;ll also need to log into your target host and ensure that you can use SSH keys to authenticate. Platoon does not support password authentication. You&#39;ll also need to ensure your web server can serve a symbolic link. If you&#39;re an Nginx user, there&#39;s likely not much you&#39;ll need to change, but for Apache, you&#39;ll need to ensure you have the <code>+FollowSymLinks</code> option set.</p><p>In your <code>platoon.php</code> config file, you need to provide a <code>path</code> setting. This is NOT the path you should point your web server at. Instead, this is the ROOT path where platoon will place everything related to your project.</p><p>Your web server will need to configured to serve the <code>live</code> symbolic link that Platoon will create during deployment. So for example, if your application root is <code>/opt/my/application</code>, then your web server should serve <code>/opt/my/application/live</code>. The <code>live</code> symbolic link will point to a directory Platoon will create in the <code>releases</code> directory.</p>',4),g=t(`<h2 id="deploy" tabindex="-1"><a class="header-anchor" href="#deploy" aria-hidden="true">#</a> Deploy</h2><p>Once you&#39;re all configured and your target has been set up, you&#39;re ready to deploy. Platoon provides a simple command through Artisan. To deploy, simply run:</p><div class="language-bash line-numbers-mode" data-ext="sh"><pre class="language-bash"><code>php ./artisan platoon:deploy
</code></pre><div class="line-numbers" aria-hidden="true"><div class="line-number"></div></div></div><p>If you have more than one target in your config file, you can specify the target you want to deploy to:</p><div class="language-bash line-numbers-mode" data-ext="sh"><pre class="language-bash"><code>php ./artisan platoon:deploy staging
</code></pre><div class="line-numbers" aria-hidden="true"><div class="line-number"></div></div></div><p>By default, Platoon will select the first target in the list, otherwise you can specify the name of your default target by setting the <code>default</code> config option. Running <code>platoon:deploy</code> without specifying the target will automatically use the default target.</p><p>That&#39;s it. Your first deployment is done. You&#39;ll probably want to log back into your target and update the <code>.env</code> file and migrate databases and such. In future, new deployments will create a new directory in the <code>releases</code> directory and replace the <code>live</code> symbolic link.</p><h2 id="environment" tabindex="-1"><a class="header-anchor" href="#environment" aria-hidden="true">#</a> Environment</h2><p>Platoon will place the <code>.env</code> file directly in the project root and create a symbolic link to it in each release. You never have to worry about your <code>.env</code> file being overwritten by a release and you have a single location when you need to update things. The same is done for the <code>storage</code> directory.</p><p>Since they are placed outside the releases, they&#39;re never overwritten. This is what makes the zero-downtime part work.</p><p>During your first deployment, Platoon will copy the storage directory from the deployment to the project root and delete the original. A new symlink will be created in each release.</p><h2 id="a-note-on-migrations" tabindex="-1"><a class="header-anchor" href="#a-note-on-migrations" aria-hidden="true">#</a> A note on migrations</h2><p>Platoon can migrate database changes for you. However, it&#39;s good to know that it does so by passing the <code>--force</code> parameter to the <code>migrate</code> Artisan command. This could potentially by unsafe, as you&#39;re manipulating databases in production. The option is provided to you, but be cautious.</p><p>In addition, Your app will likely not be configured correctly to migrate databases during that first deployment. It&#39;s understood you will update your database configuration in the <code>.env</code> file after first deployment and run the migrate command yourself. Future migrations can then happen automatically.</p><p>During first deployment, Platoon will copy the <code>.env.example</code> file. It&#39;s unlikely that you will have a database configured in this file which will cause Platoon to fail if you have <code>migrate</code> set to <code>true</code>.</p>`,15);function m(f,v){const i=a("ExternalLinkIcon"),r=a("RouterLink");return d(),s("div",null,[h,o("p",null,[e("You'll want to spend a bit of time going through the config file and the "),o("a",p,[e("Config Reference"),n(i)]),e(". However, the most important part is to ensure your targets are set up correctly.")]),y,o("p",null,[e("For a more detailed explanation of the directory structure, take a look at the "),n(r,{to:"/reference/config.html#directory-structure"},{default:c(()=>[e("Directory Structure")]),_:1}),e(" section of the config reference.")]),g])}const w=l(u,[["render",m],["__file","getting_started.html.vue"]]);export{w as default};
