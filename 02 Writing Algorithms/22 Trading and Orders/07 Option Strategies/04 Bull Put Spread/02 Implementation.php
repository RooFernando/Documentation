<p>Follow these steps to implement the bull put spread strategy:</p>

<ol>
    <li>In the <code>Initialize</code> method, set the start date, end date, cash, and <a href="/docs/v2/writing-algorithms/universes/equity-options">Option universe</a>.</li>
    <div class="section-example-container">
        <pre class="csharp">private Symbol _symbol;

public override void Initialize()
{
    SetStartDate(2017, 2, 1);
    SetEndDate(2017, 3, 5);
    SetCash(500000);

    var option = AddOption("GOOG", Resolution.Minute);
    _symbol = option.Symbol;
    option.SetFilter(universe =&gt; universe.IncludeWeeklys()
                                         .Strikes(-15, 15)
                                         .Expiration(TimeSpan.FromDays(0), TimeSpan.FromDays(31)));
}</pre>
        <pre class="python">def Initialize(self) -&gt; None:
    self.SetStartDate(2017, 2, 1)
    self.SetEndDate(2017, 3, 5)
    self.SetCash(500000)

    option = self.AddOption("GOOG", Resolution.Minute)
    self.symbol = option.Symbol
    option.SetFilter(self.UniverseFunc)

def UniverseFunc(self, universe: OptionFilterUniverse) -&gt; OptionFilterUniverse:
    return universe.IncludeWeeklys().Strikes(-15, 15).Expiration(timedelta(0), timedelta(31))</pre>
    </div>

    <li>In the <code>OnData</code> method, select the expiration and strikes of the contracts in the strategy legs.</li>
    <div class="section-example-container">
        <pre class="csharp">public override void OnData(Slice slice)
{
    if (Portfolio.Invested) return;

    // Get the OptionChain
    var chain = slice.OptionChains.get(_symbol, null);
    if (chain.Count() == 0) return;

    // Get the furthest expiration date of the contracts
    var expiry = chain.OrderByDescending(x =&gt; x.Expiry).First().Expiry;
    
    // Select the put Option contracts with the furthest expiry
    var puts = chain.Where(x =&gt; x.Expiry == expiry &amp;&amp; x.Right == OptionRight.Put);
    if (puts.Count() == 0) return;

    // Select the ITM and OTM contract strikes from the remaining contracts
    var putStrikes = puts.Select(x =&gt; x.Strike).OrderBy(x =&gt; x);
    var itmStrike = putStrikes.Last();
    var otmStrike = putStrikes.First();</pre>
        <pre class="python">def OnData(self, slice: Slice) -&gt; None:
    if self.Portfolio.Invested: return

    # Get the OptionChain
    chain = slice.OptionChains.get(self.symbol, None)
    if not chain: return

    # Get the furthest expiration date of the contracts
    expiry = sorted(chain, key = lambda x: x.Expiry, reverse=True)[0].Expiry
    
    # Select the put Option contracts with the furthest expiry
    puts = [i for i in chain if i.Expiry == expiry and i.Right == OptionRight.Put]
    if len(puts) == 0: return

    # Select the ITM and OTM contract strikes from the remaining contracts
    put_strikes = sorted([x.Strike for x in puts])
    otm_strike = put_strikes[0]
    itm_strike = put_strikes[-1]</pre>
    </div>

    <li>In the <code>OnData</code> method, call the <code>OptionStrategies.BullPutSpread</code> method and then submit the order.</li>
    <div class="section-example-container">
        <pre class="csharp">var optionStrategy = OptionStrategies.BullPutSpread(_symbol, itmStrike, otmStrike, expiry);
Buy(optionStrategy, 1);<br></pre>
        <pre class="python">option_strategy = OptionStrategies.BullPutSpread(self.symbol, itm_strike, otm_strike, expiry)
self.Buy(option_strategy, 1)</pre>
    </div>

<?php 
$methodNames = array("Buy");
include(DOCS_RESOURCES."/trading-and-orders/option-strategy-extra-args.php"); 
?>

</ol>
