Documentation Instructions
--------------------------
We use `phpdox` to generate rich API documentation of the SMR source code.

To generate the documentation, run:

```
docker-compose run --rm phpdox
```

This will build the docker image and run phpdox. On subsequent calls, it
will pick up any changes except to `Dockerfile`. If you ever need to modify
`Dockerfile`, make sure to run `docker-compose build` afterwards.

To view the documentation locally, run:

```
some-browser ./api/html/index.xhtml
```

Where `some-browser` is your browser of choice.
