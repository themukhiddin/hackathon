from flask import Flask
from pymystem3 import Mystem
from flask import request

app = Flask(__name__)

@app.route('/mystem')
def hello_world():
    text = request.args.get('text')
    m = Mystem()
    lemmas = m.lemmatize(text)
    return {'text': ''.join(lemmas)}

if __name__ == '__main__':
    app.run(host='0.0.0.0', debug=True)
