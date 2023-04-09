template.helper('jQuery',function(){return jQuery;});
template.helper("webroot", function(path)
{
	if(!path || typeof(path) != 'string')
	{
		return;
	}
	return path.indexOf('http') == 0 ? path : domain+path;
});
template.helper('window',function(){return window;});