# TIO Project

Simple Event/Ticket system written in Symfony with API and testing.

## Setup

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up` (the logs will be displayed in the current shell)
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## API Documentation
### Events
#### GET /api/events
Returns all events

##### Response
```json
{
    "count": 1,
    "events": [
        {
            "id": 1,
            "title": "Test Event",
            "date": "2022-12-12T00:00:00+00:00",
            "city": "Test City",
            "tickets_count": 1
        }
    ]
}
```
----
#### GET /api/event/{EVENT_ID}
Returns a specific event by ID

##### Response (Successful)
```json
{
    "id": 1,
    "title": "Test Event",
    "date": "2022-12-12T00:00:00+00:00",
    "city": "Test City",
    "tickets_count": 1,
    "tickets": [
        {
            "id": 1,
            "firstName": "Laura",
            "lastName": "Heimann"
        }
    ]
}
```

##### Response (Unsuccessful)
```json
{
    "slug": "event_not_found",
    "message": "There is no event with this ID"
}
```
----
#### POST /api/events
Creates a new event

##### Body
```json
{
    "title": "Test Event",
    "date": "2022-12-12T00:00:00+00:00",
    "city": "Test City"
}
```

##### Response (Successful)
```json
{
    "id": 1,
    "title": "Test Event",
    "date": "2022-12-12T00:00:00+00:00",
    "city": "Test City",
    "tickets_count": 0
}
```

##### Response (Unsuccessful)
```json
{
    "slug": "parameters_missing",
    "message": "Not all required parameters were set"
}
```
#### POST /api/event/{EVENT_ID}
Updates an event

##### Body
```json
{
    "title": "Test Event",
    "date": "2022-12-12T00:00:00+00:00",
    "city": "Test City"
}
```

##### Response (Successful)
```json
{
    "id": 1,
    "title": "Test Event",
    "date": "2022-12-12T00:00:00+00:00",
    "city": "Test City",
    "tickets_count": 0
}
```

##### Response (Unsuccessful)
```json
{
    "slug": "event_not_found",
    "message": "There is no event with this ID"
}
```
#### DELETE /api/events/{EVENT_ID}
Removes an event

##### Response (Successful)
```
HTTP 200
```

##### Response (Unsuccessful)
```json
{
    "slug": "event_not_found",
    "message": "There is no event with this ID"
}
```

### Tickets
#### GET /api/tickets
Returns all tickets

##### Response
```json
{
    "count": 1,
    "tickets": [
        {
            "id": 1,
            "firstName": "Laura",
            "lastName": "Heimann",
            "event": {
                "id": 1,
                "title": "Test Event",
                "date": "2022-12-12T00:00:00+00:00",
                "city": "Test City",
                "tickets_count": 1
            }
        }
    ]
}
```
----
#### GET /api/ticket/{TICKET_ID}
Returns a specific ticket by ID

##### Response (Successful)
```json
{
    "id": 1,
    "firstName": "Laura",
    "lastName": "Heimann",
    "event": {
        "id": 1,
        "title": "Test Event",
        "date": "2022-12-12T00:00:00+00:00",
        "city": "Test City",
        "tickets_count": 1
    }
}
```

##### Response (Unsuccessful)
```json
{
    "slug": "ticket_not_found",
    "message": "There is no ticket with this ID"
}
```
----
#### POST /api/ticket/checkBarcode
Checks if a barcode is correct and returns the ticket

##### Body
```json
{
    "barcode": "TestTest"
}
```

##### Response (Successful)
```json
{
    "id": 1,
    "firstName": "Laura",
    "lastName": "Heimann",
    "event": {
        "id": 1,
        "title": "Test Event",
        "date": "2022-12-12T00:00:00+00:00",
        "city": "Test City",
        "tickets_count": 1
    }
}
```

##### Response (Unsuccessful)
```json
{
    "slug": "ticket_not_found",
    "message": "There is no ticket with this ID"
}
```
----
#### POST /api/tickets
Creates a new ticket

##### Body
```json
{
    "eventId": 1,
    "firstName": "Laura",
    "lastName": "Heimann"
}
```

##### Response (Successful)
```json
{
    "id": 1,
    "firstName": "Laura",
    "lastName": "Heimann",
    "event": {
        "id": 1,
        "title": "Test Event",
        "date": "2022-12-12T00:00:00+00:00",
        "city": "Test City",
        "tickets_count": 1
    }
}
```

##### Response (Unsuccessful)
```json
{
    "slug": "event_not_found",
    "message": "There is no event with this ID"
}
```
```json
{
    "slug": "parameters_missing",
    "message": "Not all required parameters were set"
}
```
#### POST /api/ticket/{TICKET_ID}
Updates a ticket

##### Body
```json
{
    "firstName": "Laura",
    "lastName": "Heimann"
}
```

##### Response (Successful)
```json
{
    "id": 1,
    "firstName": "Laura",
    "lastName": "Heimann",
    "event": {
        "id": 1,
        "title": "Test Event",
        "date": "2022-12-12T00:00:00+00:00",
        "city": "Test City",
        "tickets_count": 1
    }
}
```

##### Response (Unsuccessful)
```json
{
    "slug": "ticket_not_found",
    "message": "There is no ticket with this ID"
}
```
```json
{
    "slug": "parameters_missing",
    "message": "Not all required parameters were set"
}
```
#### DELETE /api/tickets/{TICKET_ID}
Removes a ticket

##### Response (Successful)
```
HTTP 200
```

##### Response (Unsuccessful)
```json
{
    "slug": "ticket_not_found",
    "message": "There is no ticket with this ID"
}
```

## Credits
- Symfony Docker Bootstrap by [Kévin Dunglas](https://dunglas.fr)
- Uses [MaterialDesignIcons](https://materialdesignicons.com)
- Uses html5doctor.com Reset Stylesheet by [Richard Clark](http://richclarkdesign.com)
