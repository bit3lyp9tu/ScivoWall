# ScaDS poster generator (tbd)

Idea is to have a centralized poster generator, which allows visual editing of
scientific poster with LaTeX, images, lists and so on (almost full markdown support).

Currently, the data is saved into a directory outside of docker, that means before you
first run it, you have to compile the image, and then you have to run:

```console
bash docker.sh --local-port 1112
```

```console
docker exec -it poster_generator_poster_generator_1 bash
```


```console
chown -R www-data:www-data /poster_generator_json/
```

# CAVEAT

This is NOT finished yet! Someone else has to finish it, since I do not have the time
to do so.
