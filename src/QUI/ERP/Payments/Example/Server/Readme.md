# Simulated Payment Provider

This directory contains the provider-side page used by the example payment flow. It simulates an external payment service so the gateway hand-off and return paths can be demonstrated without processing a real payment.

Do not copy this simulated endpoint into a production integration. A real payment provider must be accessed through its authenticated API and requires verified signatures or webhooks, idempotency, robust error handling, and operational monitoring.
