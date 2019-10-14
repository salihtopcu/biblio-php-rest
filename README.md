# biblio-php-rest
Biblio PHP-Rest is a basic substructure for PHP Rest services.

<a href="https://miro.com/welcomeonboard/6xv072vmFoSXtBCdgJzrTYKrbIXtA990XMQYmcUJiGCl9h562UczxItTrSTt20RU">Visual Structure</a>

<h3>http/Session</h3>
<p>Session is an abstract class that should be the parent class of the main instance of the rest service. So the rest service project should define a CustomizedSession class and an instance that created from this class. Biblio recognizes every request-response lifetimes as a session.
  
A Session must have a RequestHandler and a ResponseBuilder.
</p>

<h3>http/RequestHandler</h3>
<p>RequestHandler is an abstract class either like Session. The rest service project must define a RequestHandler class too. By the way you must know api module files path to be able to redirect requests to related api methods.</p>

<h3>http/ResponseBuilder</h3>
<p>ResponseBuilder is the class that defines the instance that Session must have one and Session can send responses via this instance.</p>

<p>P.S.: You can see the relation of these 3 classes in the <a href="https://miro.com/welcomeonboard/6xv072vmFoSXtBCdgJzrTYKrbIXtA990XMQYmcUJiGCl9h562UczxItTrSTt20RU">visual structure</a></p>
