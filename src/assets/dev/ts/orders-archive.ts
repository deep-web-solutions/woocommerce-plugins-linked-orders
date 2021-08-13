import * as jQuery from 'jquery';

jQuery( function( $ ) {
    $( 'a.wc-action-button-view-all-customer-orders' ).each( function() {
        $(this).attr( 'target', '_blank' );
    } );
} );
