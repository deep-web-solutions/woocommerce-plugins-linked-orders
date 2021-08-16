import * as jQuery from 'jquery';

jQuery( function( $ ) {
    $( 'a.view-all-customer-orders, a.view-all-linked-orders' ).each( function() {
        $(this).attr( 'target', '_blank' );
    } );
} );
