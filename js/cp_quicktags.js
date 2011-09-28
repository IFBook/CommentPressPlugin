// NOTE: in WP 3.3, quicktags calls will look like this:
//QTags.addButton( 'commentblock', 'c-block', '\n<!--commentblock-->\n' );

// Commentpress commentblock custom quicktag
edButtons[edButtons.length] =
new edButton('commentblock'
,'c-block'
,'\n<!--commentblock-->\n'
,''
,'-1'
);
