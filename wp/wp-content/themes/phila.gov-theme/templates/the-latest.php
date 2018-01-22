<?php
/**
 * Template Name: The latest
 * Description: Custom Page template for The Latest
 * @package phila-gov
 */

  get_header();
?>

<div id="primary" class="the-latest content-area">
  <main id="main" class="site-main">
    <header>
      <div class="grid-container">
        <?php the_title( '<h1 class="contrast">', '</h1>' );  ?>
      </div>
      <div data-sticky-container class="bg-white">
        <nav class="sticky sticky--in-page center bg-white menu" data-sticky data-top-anchor="global-sticky-nav:bottom" style="width:100%" data-sticky-on="medium">
          <div class="grid-container">
            <ul class="inline-list grid-x man" data-magellan data-options="deepLinking: true;">
              <li class="featured cell medium-auto small-24">
                <a href="#featured">
                  <i class="fal fa-fw fa-3x fa-newspaper mbm" aria-hidden="true"></i>
                  <div>Featured</div>
                </a>
              </li>
              <li class="announcement cell medium-auto small-24">
                <a href="#announcements">
                  <i class="fal fa-fw fa-3x fa-bullhorn mbm" aria-hidden="true"></i>
                  <div>Announcements</div>
                </a>
              </li>
              <li class="post cell medium-auto small-24">
                <a href="#posts">
                  <i class="fas fa-fw fa-3x fa-pencil-alt mbm" aria-hidden="true"></i>
                  <div>Posts</div>
                </a>
              </li>
              <li class="action_guide cell medium-auto small-24">
                <a href="#action-guides">
                  <i class="fas fa-fw fa-3x fa-users mbm" aria-hidden="true"></i>
                  <div>Action guides</div>
                </a>
              </li>
              <li class="event cell medium-auto small-24">
                <a href="#events">
                  <i class="fal fa-fw fa-3x fa-calendar-check mbm" aria-hidden="true"></i>
                  <div>Events</div>
                </a>
              </li>
              <li class="press_release cell medium-auto small-24">
                <a href="#press-releases">
                  <i class="fal fa-fw fa-3x fa-file-alt mbm" aria-hidden="true"></i>
                <div>Press releases</div>
              </a>
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </header>

    <section>
      <div id="featured" data-magellan-target="featured">
        <header class="row columns mtl">
          <h1>Featured</h1>
        </header>
        <?php get_template_part('partials/posts/featured', 'grid'); ?>
      </div>
    </section>

    <section>
      <div id="announcements" data-magellan-target="announcements">
        <header class="row columns mtl">
          <h1>Announcements</h1>
        </header>
        <?php get_template_part('partials/posts/announcements', 'grid'); ?>
      </div>
    </section>

    <section>
      <div id="posts" data-magellan-target="posts">
        <header class="row columns mtl">
          <h1>The latest posts from departments</h1>
        </header>
        <?php get_template_part('partials/posts/post', 'grid'); ?>
      </div>
    </section>

    <section>
      <div id="action-guides" data-magellan-target="action-guides">
        <header class="row columns mtl">
          <h1>Action guides</h1>
        </header>
        <?php get_template_part('partials/posts/action-guide', 'grid'); ?>
      </div>
    </section>

    <section>
      <div id="events" data-magellan-target="events">
        <header class="row columns mtl">
          <h1>Upcoming events</h1>
        </header>
        <div class="grid-container">
          <div class="grid-x">
            <div class="cell small-24">
              <?php echo do_shortcode('[calendar id="28661"]')?>
            </div>
          </div>
        </div>
        <div class="grid-container">
          <div class="grid-x">
            <div class="cell small-24">
              <a href="/the-latest/all-events" class="button float-right content-type-events">See all events</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section>
      <div id="press-releases" data-magellan-target="press-releases">
        <header class="row columns mtl">
          <h1>Press Releases</h1>
        </header>
        <?php get_template_part('partials/posts/press-release', 'grid'); ?>
      </div>
    </section>

  </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
