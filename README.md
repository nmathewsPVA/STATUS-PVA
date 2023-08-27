# STATUS-PVA
Internal crew status board that pulls from the Monroe County Ambulance Tracker.

## Configuration
This application runs inside a Docker container. The following terminal commands are required to run this app locally:

- `docker`
- `docker-compose`
- `make`

Additionally, a configuration file called `.env` must be created in the repo's root directory. An example of this file
with required configuration variables can be found in the `.env.example` file.

* `API_HOST`: The hostname of the API server.
* `API_TOKEN`: The authentication token for the API.

## Running Locally

To start the app, run the following command in the repo's root directory:

```shell
make
```

The app will run on port 80 and should be seen at `http://localhost/`.

To stop the app, run the following command in the repo's root directory:

```shell
make clean
```
