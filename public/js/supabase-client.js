// Este arquivo cria e exporta uma ÚNICA instância do cliente Supabase.
import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/+esm';

const supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkyNTcwNTcsImV4cCI6MjA2NDgzMzA1N30.t6b1vtcZhGfOfibwdWKLDUJq2BoRegH5s6P5_OvRwz8';

export const supabase = createClient(supabaseUrl, supabaseKey);