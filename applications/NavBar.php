<?php /*
Standard navbar for application/tools pages.

Set up an array called $navSetup with 'tabs' and 'current' keys like this:

$navSetup = [ 'tabs' => [ 'First Page' => 'page1.php',
                          'Second Page' => 'http://otherhost.com/p2.html',
                          'Third Nav' => 'page1.php?showthird=true'
                        ],
              'current' => 'First Page'
            ]

..and you'll get a nav bar that looks okay.  You can set 'current'
to either the key or value of your page, so if the current page is
'First Page', you could also set current to 'page1.php' and it would
still work.
*/ ?>
<div id="navbarwrapper" class="navbarwrapper clearfix">
    <ul class="nav clearfix">
        <?php foreach ($navSetup['tabs'] as $name => $url) {
            echo '<li><a class="';
            if (($name == $navSetup['current']) || ($url == $navSetup['current'])) {
                echo 'current';
            }
            echo '" href="' . $url . '">' . $name . '</a></li>';
        } ?>
    </ul>
</div>