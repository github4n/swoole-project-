alter view deposit_passage_gate_intact as
    select a.passage_id,a.passage_name,a.risk_control,a.cumulate,a.acceptable,
        g.gate_key,g.gate_name,g.api_url,g.account_number,g.account_name,g.jump_url,g.signature_key,g.encrypt_key
    from deposit_passage a inner join deposit_passage_gate g on a.passage_id=g.passage_id
;;