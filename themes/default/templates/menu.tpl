<!-- Start Navigator -->
<div id="main-nav">
<ul>
{foreach from=$Core_NavigationMenu key=mainMenu item=subMenu name=menu}
    <li id="{$mainMenu}" class="level1">
        <a href="#">{$mainMenu}</a>
        <ul>
        {foreach from=$subMenu key=name item=urlParams name=subMenu}
            {if strpos($name, '_') !== 0}
                <li class="level2 hidden"><a href="{$urlParams._url|@urlRewriteBasicView}">{$name}</a></li>
            {/if}
        {/foreach}
        </ul>
    </li>
{/foreach}
</ul>
</div> <!-- main-nav -->
<!-- End Navigator -->
