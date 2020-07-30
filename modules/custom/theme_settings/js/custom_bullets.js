( function( $, wb ) {
"use strict";

  function initListIcon() {
    //console.log('run initListIcon');
    $('.list-icon').each(function( event ){
      var $this = $(this);
      var items = $this.find('li').not('.icon-loaded');
      var icon = $this.attr('data-icon');
      if (!$this.is("ul") && !$this.is("ol")) {
        $this.removeClass('list-icon');
        $this = items.parent('ul, li');
        $this.attr('data-icon', icon);
      }
      $this.addClass('list-icon list-group lst-spcd mrgn-lft-md mrgn-bttm-md list-unstyled').addClass('list-icon-initialized');
      //console.log(items, $this, icon);
      if (icon.length <= 0) {
        return;
      }
      items.prepend('<i class="fa-li fa ' + icon + '" aria-hidden="true"></i>').addClass('icon-loaded');
    });
  }

  Drupal.behaviors.initListIcon = {
    attach: function (context) {
      initListIcon();
    }
  };

  $( document ).on( "wb-ready.wb", function( event) {
    initListIcon();
  });

} )( jQuery, wb );
