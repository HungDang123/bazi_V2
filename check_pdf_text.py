import pypdf
import sys

path = r"storage/app/temp/vn-font-test.pdf"
r = pypdf.PdfReader(path)
t = "".join((p.extract_text() or "") for p in r.pages)
sys.stdout.buffer.write(t.encode("utf-8", errors="replace"))
sys.stdout.buffer.write(b"\n---\n")
sys.stdout.buffer.write(f"question_marks={t.count('?')}\n".encode())
sys.stdout.buffer.write(f"has_ă={'ă' in t}\n".encode())
sys.stdout.buffer.write(f"has_đ={'đ' in t}\n".encode())
