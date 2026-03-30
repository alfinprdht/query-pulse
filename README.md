## Query Pulse

Run the following command :

``composer update alfinprdht/query-pulse``

``php artisan vendor:publish --tag=performance-query-inspector-config``

## Configurations

### Enable the Query Pulse

Set the value of this environment variable ``QUERY_PULSE_ENABLED`` to true

### Threshold

- QUERY_PULSE_SLOW_QUERY_TIME : the threshold time for each query time (ms)
- QUERY_PULSE_DUPLICATE_BURST : number of similar fingerprints within one request, 
- QUERY_PULSE_PROBABLE_N_PLUS_1 : number of similar query patterns with different bindings
- QUERY_PULSE_TOTAL_QUERY_TIME : total of query time executed in one request (ms)
- QUERY_PULSE_TOTAL_QUERY_COUNT : number of queries executed in one request

## Usage

After enabling the query pulse, it will automatically create a database listener on every URL hit on any request method. The data recorded to built in database table with this package. 

### Via Commands

To see the result, just run :

``php artisan query-pulse:url '{{METHOD + ' ' + URL}}'``

for example

``php artisan query-pulse:url 'GET dashboard/overview'``

Result example :

```http
[Query Pulse]
URL: GET orders/1702
--------------------------------------------------
Average query time: 378.676 ms
Slow Query: 1
Duplicate Burst: 1
Probable N+1: 1
Supicious Wildcard Fetch: 65
Total Query Time: 621.51 ms
Total Query Count: 72
Score: 47 (POOR)
```



### Scoring Method

From 100, it will be reduced based on the following accumulation:

- Slow query: 10 x number of query time thresholds crossed
- Duplicate burst: 10 x number of duplicate burst thresholds crossed
- Probable N+1: 10
- Suspicious Wildcard Fetch: number of wildcards fetch / 5
- Total Query Time: 10 if the total query time crossed the thresholds
- Total Query Count: 10 if the total query count crossed the thresholds

Scoring category based on accumulation above :

- Healthy : 90 - 99
- Watch : 70 - 89
- Poor : 40 - 69
- Critical : < 39
