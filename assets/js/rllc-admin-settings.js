( function ( $ ) {
    "use strict";
    function insertAtCaret( areaId, text ) {
        var txtarea     = $( areaId ).get( 0 );
        var scrollPos   = txtarea.scrollTop;
        var strPos      = 0;
        var br          = ( ( txtarea.selectionStart || txtarea.selectionStart == '0' ) ? "ff" : ( document.selection ? "ie" : false ) );
        if ( br == "ie" ) {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart( 'character', -txtarea.value.length );
            strPos = range.text.length;
        } else if ( br == "ff" ) strPos = txtarea.selectionStart;

        var front = ( txtarea.value ).substring( 0, strPos );
        var back  = ( txtarea.value ).substring( strPos, txtarea.value.length );
        txtarea.value = front + text + back;
        strPos = strPos + text.length;
        if ( br == "ie" ) {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart( 'character', -txtarea.value.length );
            range.moveStart( 'character', strPos );
            range.moveEnd( 'character', 0 );
            range.select();
        } else if ( br == "ff" ) {
            txtarea.selectionStart = strPos;
            txtarea.selectionEnd = strPos;
            txtarea.focus();
        }
        txtarea.scrollTop = scrollPos;
    }

    $( document ).ready( function () {
        var $form = $( 'form#mainform' ),
                $sub = $( '.subsubsub' ),
                data = $form.serialize();

        $sub.find( 'a[href*="tab=rllc"]' ).click( function ( e ) {
            e.preventDefault();
            if ( $sub.hasClass( 'saving' ) ) {
                return;
            }
            var $el = $( this ),
                    newData = $form.serialize();
            if ( newData != data ) {
                var $spinner = $( '<span class="spinner"></span>' ).css( {
                    visibility: 'visible',
                    float: 'none',
                    'vertical-align': 'top',
                    'margin-right': 0
                } );
                $el.closest( '.subsubsub' ).find( '.current' ).append( $spinner );
                $.ajax( {
                    url: window.location.href,
                    data: newData,
                    type: 'post',
                    dataType: 'text',
                    success: function () {
                        $spinner.fadeOut();
                        window.location.href = $el.attr( 'href' );
                    }
                } );
                $sub.addClass( 'saving' );
            } else {
                window.location.href = $el.attr( 'href' );
            }
        } );

        $( 'code.rllc-clickable' ).click( function () {
            insertAtCaret( $( this ).closest( '.forminp' ).find( 'input[type="text"]' ), this.innerHTML );
        } );
    } );
} )( jQuery );