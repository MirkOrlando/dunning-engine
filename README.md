# Dunning Engine
dunning-engine is a SaaS subscription payment engine: it manages auto-retry payments with grace days.
Recurring payments fail continously due to trivial reasons (expired card, insufficient funds, etc...) and a reliable system must manage these occurency with a defined and accurate logic.
- Manages monthly billing cycles;
- Simulate payment outcomes and manage grace-period retries;
- Use a panel to observe subscription status and force events manually.