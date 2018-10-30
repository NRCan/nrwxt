( function( $ ) {
"use strict";

  function ga_add_debug_info($this, options) {
    var eventLabel = options.label;
    var eventAction = options.action;
    var eventValue = options.value;
    var value = 0;
    // Apply the specific options

    if (options.mode == 'text') {
      eventLabel = $this.attr("title");
      if (eventLabel == undefined || eventLabel == '') {
        eventLabel = $this.text();
      }
    }
    if (eventLabel == undefined || eventLabel == '') {
      eventLabel = $this.attr('href');
    }

    value = $this.attr('data-ga-index');
    if (options.increment == 'action') {
      if (value != null) {
        eventAction = options.action + ' ' + value;
      }
    }
    if (value != null) {
      eventValue = value;
    }

    $this.append(`<!--
    DEBUG Details for Google Analytics. On click this will be triggered:
    ga("send", {
        "hitType": "event",
        "transport": "beacon",
        "eventCategory": "${options.category}",
        "eventAction": "${eventAction}",
        "eventLabel": "${eventLabel}",
        "eventValue": "${eventValue}"
      }); -->`);
  }

  function getGAHandler(options) {
    //console.log('Create GA Handler', category, label);
    return function ( event ) {
      if (event.type == 'mousedown' && event.which != 1) {
        //only trigger on left click.
        return;
      }
      $(event.target).closest("a").each(function() {
        console.log('Call GA Handler', event, options);
        var eventLabel = options.label;
        var eventAction = options.action;
        var eventValue = options.value;
        var $this = $(this);
        var value = 0;
        // Apply the specific options

        if (options.mode == 'text') {
          eventLabel = $this.attr("title");
          if (eventLabel == undefined || eventLabel == '') {
            eventLabel = $this.text();
          }
        }
        if (eventLabel == undefined || eventLabel == '') {
          eventLabel = $this.attr('href');
        }

        value = $this.attr('data-ga-index');
        if (options.increment == 'action') {
          if (value != null) {
            eventAction = options.action + ' ' + value;
          }
        }
        if (value != null) {
          eventValue = value;
        }

        console.log('GA TRIGGER: event', options.category, eventAction, eventLabel, eventValue);
        ga("send", {
          "hitType": "event",
          "transport": "beacon",
          "eventCategory": options.category,
          "eventAction": eventAction,
          "eventLabel": eventLabel,
          "eventValue": eventValue
        });
      });
    };

  }

  function initGALinks() {
    //console.log('run GA Handler');
    $("[data-ga]").not('initialized').addClass('initialized').each(function( event ){
      var $this = $(this),
          data = $this.attr('data-ga');
      data = JSON.parse(data);
      var links = $this.find('a');
      if ($this.is('a')) {
        links = $this;
      }

      // Default options;
      var options = {
          category: 'Click',
          action: 'Link',
          label: undefined,
          mode: 'text', // url or text
          value: 0,
          increment: 'action' // none, value or action
        };
        var defaults = options;
        $.extend( options, data );

      // Assign Indexes
      var url_lookup = {};
      var link_index = 1;
      links.each(function(){
        var $this = $(this);
        if ($this.hasClass('contextual-links-trigger')) {
          return;
        }
        if ($this.parent().hasClass('edit-link')) {
          return;
        }
        var href = $this.attr('href');
        var current_index = $this.attr('data-ga-index');
        if (!(href in url_lookup)) {
          if (current_index != null) {
            url_lookup[href] = current_index;
          } else {
            url_lookup[href] = link_index;
            link_index++;
          }
        }
        //$this.attr('data-ga-category', data.category);
        $this.attr('data-ga-index', url_lookup[href]);
        if (!$this.hasClass('debug-init') && $('body').hasClass('admin-menu')) {
          ga_add_debug_info($this, options);
          $this.addClass('debug-init');
        }
      });
      $this.on("mousedown keyup touchstart", getGAHandler(options));
    });
  }

  Drupal.behaviors.initListIcon = {
    attach: function (context) {
      initGALinks();
    }
  };

} )( jQuery );
