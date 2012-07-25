<?php
// $Id$
// (c) 2010 Pyramid Power, Australia


// Wiki model exceptions
class WikiException extends Exception { }
class WikiNoAccessException extends WikiException { }
class WikiExistsException extends WikiException { }