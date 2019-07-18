<html>

<head>
    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
</head>

<body>

<header class="navbar">
    <section class="navbar-section">
        <div class="centered">
            <h1 class="">{{#apiMetaData}}{{title}}{{/apiMetaData}}{{^apiMetaData}}API Documentation{{/apiMetaData}}</h1>
        </div>
    </section>
</header>

<hr>


{{#apis}}

Please select an API to explore the documentation.

{{/apis}}


{{#apiMetaData}}


<div class="container">
    <div class="columns">
        <div class="column col-3">

            <h4><a href="/apidoc">API Home</a></h4>

            <h4>APIs</h4>

            <ul>
                {{#controllerSummaries}}

                <li>
                    <a href="/apidoc/{{identifier}}/api/{{path}}">{{title}}</a>
                </li>

                {{#active}}
                <ul>
                    {{#methodNames}}
                    <li><a href="/apidoc/{{identifier}}/api/{{path}}/{{.}}">{{.}}</a></li>
                    {{/methodNames}}
                </ul>
                {{/active}}


                {{/controllerSummaries}}
            </ul>

            <h4>Objects</h4>
            <ul>
                {{#objectSummaries}}
                <li><a href="/apidoc/{{identifier}}/object/{{path}}">{{name}}</a></li>
                {{/objectSummaries}}
            </ul>

            <h4>Exceptions</h4>
            <ul>
                {{#exceptionSummaries}}
                <li><a href="/apidoc/{{identifier}}/exception/{{path}}">{{name}}</a></li>
                {{/exceptionSummaries}}
            </ul>

        </div>


        <div class="column col-9">

            {{^controller}}
            {{^object}}
            {{^exception}}

            <h2>{{title}}</h2>

            <p>The following APIs are available within the {{title}} </p>

            <table class="table table-striped">
                <tbody>
                {{#controllerSummaries}}
                <tr>
                    <td><a href="/apidoc/{{identifier}}/api/{{path}}">{{title}}</a></td>
                </tr>
                {{/controllerSummaries}}
                </tbody>
            </table>

            {{/exception}}
            {{/object}}
            {{/controller}}


            {{#controller}}

            {{^method}}

            <h2>{{title}}</h2>

            <p>
                {{commentHTML}}
            </p>


            <h5>Methods</h5>

            <table class="table table-striped">

                <thead>
                <tr>
                    <th>Name</th>
                    <th>HTTP Method</th>
                    <th>Path</th>
                </tr>
                </thead>


                <tbody>
                {{#methods}}
                <tr>
                    <td>
                        <a href="/apidoc/{{identifier}}/api/{{path}}/{{name}}">{{name}}</a>
                    </td>
                    <td>
                        <span class="label label-{{#httpMethodIndicator.GET}}primary{{/httpMethodIndicator.GET}}{{#httpMethodIndicator.POST}}success{{/httpMethodIndicator.POST}}{{#httpMethodIndicator.PUT}}warning{{/httpMethodIndicator.PUT}}{{#httpMethodIndicator.PATCH}}secondary{{/httpMethodIndicator.PATCH}}{{#httpMethodIndicator.DELETE}}error{{/httpMethodIndicator.DELETE}}">{{httpMethod}}</span>
                    </td>
                    <td>
                        {{fullRequestPath}}
                    </td>
                </tr>
                {{/methods}}
                </tbody>


            </table>


            {{/method}}

            {{#method}}

            <h2>{{title}}: {{name}}</h2>

            <p>
                {{commentHTML}}
            </p>
            <p>
                <span class="label label-{{#httpMethodIndicator.GET}}primary{{/httpMethodIndicator.GET}}{{#httpMethodIndicator.POST}}success{{/httpMethodIndicator.POST}}{{#httpMethodIndicator.PUT}}warning{{/httpMethodIndicator.PUT}}{{#httpMethodIndicator.PATCH}}secondary{{/httpMethodIndicator.PATCH}}{{#httpMethodIndicator.DELETE}}error{{/httpMethodIndicator.DELETE}}">{{httpMethod}}</span>
                &nbsp;&nbsp;{{fullRequestPath}}

            </p>

            <p>
                {{#rateLimit}}<span class="label label-warning">Rate Limited: </span> {{.}} requests per {{rateLimitPeriod}}mins{{/rateLimit}}
                {{#rateLimitMultiplier}}<span class="label label-warning">Rate Limited </span> Default Rate Limit {{^rateLimitMultiplierIsOne}}* {{.}}{{/rateLimitMultiplierIsOne}}{{/rateLimitMultiplier}}
            </p>



            {{#hasParams}}
            <h5>Parameters</h5>
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
                </thead>

                <tbody>
                {{#globalParameters}}
                <tr class="active">
                    <td>{{name}}
                        <mark>required</mark>
                    </td>
                    <td>string</td>
                    <td>{{description}}</td>
                </tr>
                {{/globalParameters}}

                {{#params}}
                <tr>
                    <td>{{name}} {{^isOptional}}
                        <mark>required</mark>
                        {{/isOptional}}
                    </td>
                    <td>{{#typePath}}<a href="/apidoc/{{identifier}}/object{{.}}">{{shortType}}{{/typePath}}{{^typePath}}{{shortType}}{{/typePath}}
                    </td>
                    <td>{{description}}</td>
                </tr>
                {{/params}}
                </tbody>

            </table>
            {{/hasParams}}
            {{^hasParams}}
            <p>This method has no input parameters</p>
            {{/hasParams}}

            <p></p>

            {{#returnType}}
            <h5>Return Type: {{#returnTypePath}}<a href="/apidoc/{{identifier}}/object{{.}}">{{shortReturnType}}</a>{{/returnTypePath}}{{^returnTypePath}}{{shortReturnType}}{{/returnTypePath}}
            </h5>
            <p>{{returnDescription}}</p>
            {{/returnType}}
            {{^returnType}}
            <p>This method does not return a value</p>
            {{/returnType}}


            <h5>Exceptions:</h5>
            {{^exceptions}}
            <p>This method does not throw any explicit exceptions.</p>
            {{/exceptions}}
            {{#exceptions}}
            <p><a href="/apidoc/{{identifier}}/exception{{path}}">{{shortType}}</a></p>
            {{/exceptions}}

            <hr>

            <h5>CURL Example</h5>

            <code>
                curl -H "Content-Type: application/json" -X {{httpMethod}}
                {{baseURL}}{{fullRequestPath}}?{{#globalParameters}}{{#index}}&{{/index}}{{name}}=VALUE{{/globalParameters}}{{#params}}{{^payloadParam}}{{^segmentParam}}&{{name}}=VALUE{{/segmentParam}}{{/payloadParam}}{{/params}}
                {{#payloadParam}}-d '{ {{shortType}} JSON DATA }'{{/payloadParam}}
            </code>



            {{#availableClients.PHP}}
            <p></p>
            <h5>PHP Example</h5>

            <code>

                use {{rootNamespace}}\APIProvider;
                <br/><br/>
                $client = new APIProvider("{{baseURL}}"{{#globalParameters}}, "{{name}}"{{/globalParameters}});
                <br/><br/>
                {{#returnType}}$result = {{/returnType}} $client->{{className}}()->{{name}}({{#params}}{{#index}}, {{/index}}${{name}}{{/params}});


            </code>

            {{/availableClients.PHP}}


            {{#availableClients.Java}}
            <p></p>
            <h5>Java Example</h5>

            <code>

                APIProvider client = new APIProvider("{{baseURL}}"{{#globalParameters}}, "{{name}}"{{/globalParameters}});
                <br/><br/>
                {{#returnType}}{{returnJavaType}} result = {{/returnType}} client.{{className}}().{{name}}({{#params}}{{#index}}, {{/index}}{{name}}{{/params}});


            </code>

            {{/availableClients.Java}}


            {{/method}}


            {{/controller}}

            {{#object}}

            <h2>{{name}}</h2>

            <p>{{commentHTML}}</p>

            <h5>Properties</h5>

            <table class="table table-striped">

                <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Access</th>
                    <th>Description</th>
                </tr>
                </thead>


                <tbody>
                {{#properties}}
                <tr>
                    <td style="vertical-align: top">
                        {{name}} {{#required}} <mark>required</mark>{{/required}}
                    </td>
                    <td style="vertical-align: top">
                        {{#typePath}}<a href="/apidoc/{{identifier}}/object{{typePath}}">{{shortType}}</a>{{/typePath}}{{^typePath}}{{shortType}}{{/typePath}}
                    </td>
                    <td style="vertical-align: top">
                        {{#updatable}}Read / Write{{/updatable}}{{^updatable}}Read Only{{/updatable}}
                    </td>
                    <td>
                        {{description}}
                    </td>
                </tr>
                {{/properties}}
                </tbody>


            </table>


            <hr>

            <h5>JSON Example</h5>

                <code>
                    {
                    {{#properties}}
                    <br />"{{name}}": {{#isString}}"string value"{{/isString}}
                    {{#isArray}}{{#arrayAssociativeKey}}{ {{arrayShortType}} values keyed by {{.}} }{{/arrayAssociativeKey}}
                    {{^arrayAssociativeKey}}[{{arrayShortType}} values]{{/arrayAssociativeKey}}{{/isArray}}
                    {{^isString}}{{^isArray}}{{shortType}} value{{/isArray}}{{/isString}}
                    {{/properties}}
                <br />}
                </code>


            {{#availableClients.PHP}}
            <p></p>
            <h5>PHP Example</h5>

            <code>

                use {{namespace}}\{{name}};
                <br/><br/>
                {{#properties}} {{#updatable}}
                <br />${{name}} =
                {{#isArray}} []; // Add more items here{{/isArray}}
                {{#isString}} "STRINGVAL";{{/isString}}
                {{#isObject}} new {{shortType}}(); // Set more props here{{/isObject}}
                {{^isArray}}{{^isString}}{{^isObject}}{{shortType}}VAL;{{/isObject}}{{/isString}}{{/isArray}}
                {{/updatable}}
                {{/properties}}
                <br /><br />
                $object = new {{name}}({{#properties}}{{#updatableIndex}}, {{/updatableIndex}}{{#updatable}}${{name}}{{/updatable}}{{/properties}});

            </code>

            {{/availableClients.PHP}}


            {{#availableClients.Java}}
            <p></p>
            <h5>Java Example</h5>

            <code>import {{javaPackage}}.{{name}};
                {{#javaImports}}
                <br />import {{.}};
                {{/javaImports}}
                <br/><br/>
                {{#properties}} {{#updatable}}
                <br />{{javaHTMLType}} {{name}} =
                {{#isArray}}
                {{#arrayAssociativeKey}}new {{javaHTMLType}}(); // Add more items here{{/arrayAssociativeKey}}
                {{^arrayAssociativeKey}}new String[]{}; // Add more items here{{/arrayAssociativeKey}}
                {{/isArray}}
                {{#isString}} "STRINGVAL";{{/isString}}
                {{#isObject}} new {{shortType}}(); // Set more props here{{/isObject}}
                {{^isArray}}{{^isString}}{{^isObject}}{{shortType}}VAL;{{/isObject}}{{/isString}}{{/isArray}}
                {{/updatable}}
                {{/properties}}
                <br /><br />
                $object = new {{name}}({{#properties}}{{#updatableIndex}}, {{/updatableIndex}}{{#updatable}}{{name}}{{/updatable}}{{/properties}});
            </code>

            {{/availableClients.Java}}



            {{/object}}


            {{#exception}}

            <h2>{{name}}</h2>

            <p>{{commentHTML}}</p>

            <h5>Properties</h5>

            <table class="table table-striped">

                <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Access</th>
                    <th>Description</th>
                </tr>
                </thead>


                <tbody>
                {{#properties}}
                <tr>
                    <td style="vertical-align: top">
                        {{name}} {{#required}} <mark>required</mark>{{/required}}
                    </td>
                    <td style="vertical-align: top">
                        {{#typePath}}<a href="/apidoc/{{identifier}}/object{{typePath}}">{{shortType}}</a>{{/typePath}}{{^typePath}}{{shortType}}{{/typePath}}
                    </td>
                    <td style="vertical-align: top">
                        {{#updatable}}Read / Write{{/updatable}}{{^updatable}}Read Only{{/updatable}}
                    </td>
                    <td>
                        {{description}}
                    </td>
                </tr>
                {{/properties}}
                </tbody>


            </table>

            {{/exception}}

        </div>
    </div>
</div>


{{/apiMetaData}}


</body>


</html>